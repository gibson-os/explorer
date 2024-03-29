<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command;

use Exception;
use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Command\Option;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\EnvService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Module\Explorer\Service\FileService as ExplorerFileService;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use Psr\Log\LoggerInterface;

/**
 * @description Index all files in `home_path`
 */
#[Cronjob(hours: '3', minutes: '30', daysOfWeek: '3')]
#[Lock('explorerIndexerCommand')]
class IndexerCommand extends AbstractCommand
{
    #[Option('Renew index databases')]
    private bool $renew = false;

    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly GibsonStoreService $gibsonStoreService,
        private readonly DirService $dirService,
        private readonly FileService $fileService,
        private readonly EnvService $envService,
        private readonly DescriberFactory $describerFactory,
        private readonly ServiceManager $serviceManager,
        private readonly ExplorerFileService $explorerFileService,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws GetError
     * @throws SelectError
     */
    protected function run(): int
    {
        $homePath = $this->settingRepository->getByKeyAndModuleName('explorer', 0, 'home_path');
        $this->indexDir($homePath->getValue(), false);

        return self::SUCCESS;
    }

    /**
     * @throws GetError
     */
    private function indexDir(string $dir, bool $noStore): array
    {
        $files = [];
        $dirMetas = [
            'filesize' => 0,
            'dirsize' => 0,
            'dircount' => 0,
            'dirdircount' => 0,
            'filecount' => 0,
            'dirfilecount' => 0,
        ];

        foreach ($this->dirService->getFiles($dir) as $path) {
            if (is_dir($path)) {
                $dirMetas = $this->indexDirDir($path, $dirMetas, $noStore);

                continue;
            }

            $dirMetas['filesize'] += filesize($path);
            ++$dirMetas['filecount'];
            $files[] = $this->fileService->getFilename($path);

            if ($noStore) {
                continue;
            }

            try {
                $this->indexFile($path);
            } catch (Exception $exception) {
                echo 'FEHLER: ' . $exception->getMessage() . PHP_EOL;
            }
        }

        $dirMetas['dirsize'] += $dirMetas['filesize'];
        $dirMetas['dirfilecount'] += $dirMetas['filecount'];
        $dirMetas['dirdircount'] += $dirMetas['dircount'];

        $storeDir = mb_substr($dir, 0, -1);

        if (!$noStore) {
            try {
                $this->gibsonStoreService->cleanStore($storeDir, $files);
                $this->gibsonStoreService->setDirMetas($storeDir, $dirMetas);
                system('/usr/bin/setfacl -m u:' . $this->envService->getString('APACHE_USER') . ':rw- "' . $dir . '.gibsonStore"');
            } catch (Exception $exception) {
                echo 'FEHLER: ' . $exception->getMessage() . PHP_EOL;
            }
        }

        try {
            $this->gibsonStoreService->close($storeDir);
        } catch (Exception $exception) {
            echo 'FEHLER: ' . $exception->getMessage() . PHP_EOL;
        }

        return [
            'dirSize' => $dirMetas['dirsize'],
            'dirFileCount' => $dirMetas['dirfilecount'],
            'dirDirCount' => $dirMetas['dirdircount'],
        ];
    }

    /**
     * @throws GetError
     *
     * @return int[]
     */
    private function indexDirDir(string $path, array $dirMetas, bool $noStore): array
    {
        $noStoreDir = $noStore;

        try {
            if (
                file_exists($path . '/.noStore')
                || $this->gibsonStoreService->getDirMeta($path, 'ignore', false)
            ) {
                $noStoreDir = true;
            }
        } catch (Exception) {
            $noStoreDir = true;
        }

        $result = $this->indexDir($path . '/', $noStoreDir);
        $dirMetas['dirsize'] += $result['dirSize'];
        $dirMetas['dirfilecount'] += $result['dirFileCount'];
        $dirMetas['dirdircount'] += $result['dirDirCount'];
        ++$dirMetas['dircount'];

        return $dirMetas;
    }

    /**
     * @throws ExecuteError
     * @throws FactoryError
     * @throws GetError
     * @throws ReadError
     * @throws WriteError
     */
    private function indexFile(string $path): void
    {
        $fileTypeDescriber = $this->describerFactory->create($path);
        $fileTypeService = null;

        if (!$this->gibsonStoreService->hasFileMetas($path, $fileTypeDescriber->getMetasStructure())) {
            /** @var FileTypeInterface $fileTypeService */
            $fileTypeService = $this->serviceManager->get($fileTypeDescriber->getServiceClassname());
            $this->explorerFileService->setFileMetas($fileTypeService, $path);
        }

        if (!$this->gibsonStoreService->hasFileImage($path)) {
            if ($fileTypeService === null) {
                $fileTypeService = $this->serviceManager->get($fileTypeDescriber->getServiceClassname());
            }

            try {
                $image = $fileTypeService->getImage($path);
                $checkSum = md5_file($path);

                $this->gibsonStoreService->setFileImage($path, $image, $checkSum ?: null);
            } catch (Exception) {
                // No Image
            }
        }
    }

    public function setRenew(bool $renew): void
    {
        $this->renew = $renew;
    }
}
