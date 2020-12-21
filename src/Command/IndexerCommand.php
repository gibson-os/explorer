<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command;

use Exception;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Flock\LockError;
use GibsonOS\Core\Exception\Flock\UnlockError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\EnvService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Module\Explorer\Service\FileService as ExplorerFileService;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use Psr\Log\LoggerInterface;

class IndexerCommand extends AbstractCommand
{
    private LockService $lockService;

    private SettingRepository $settingRepository;

    private GibsonStoreService $gibsonStoreService;

    private DirService $dirService;

    private FileService $fileService;

    private EnvService $envService;

    private DescriberFactory $describerFactory;

    private ServiceManagerService $serviceManagerService;

    private ExplorerFileService $explorerFileService;

    public function __construct(
        LockService $lockService,
        SettingRepository $settingRepository,
        GibsonStoreService $gibsonStoreService,
        DirService $dirService,
        FileService $fileService,
        EnvService $envService,
        DescriberFactory $describerFactory,
        ServiceManagerService $serviceManagerService,
        ExplorerFileService $explorerFileService,
        LoggerInterface $logger
    ) {
        $this->lockService = $lockService;
        $this->settingRepository = $settingRepository;
        $this->gibsonStoreService = $gibsonStoreService;
        $this->dirService = $dirService;
        $this->fileService = $fileService;
        $this->envService = $envService;
        $this->describerFactory = $describerFactory;
        $this->serviceManagerService = $serviceManagerService;
        $this->explorerFileService = $explorerFileService;

        $this->setOption('renew');

        parent::__construct($logger);
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     * @throws UnlockError
     */
    protected function run(): int
    {
        try {
            $this->lockService->lock();

            $homePath = $this->settingRepository->getByKeyAndModuleName('explorer', 0, 'home_path');
            $this->indexDir($homePath->getValue(), false);

            $this->lockService->unlock();
        } catch (LockError $e) {
            // Indexer in progress
        }

        return 0;
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
                file_exists($path . '/.noStore') ||
                $this->gibsonStoreService->getDirMeta($path, 'ignore', false)
            ) {
                $noStoreDir = true;
            }
        } catch (Exception $exception) {
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
        $checkSum = null;

        if (!$this->gibsonStoreService->hasFileMetas($path, $fileTypeDescriber->getMetasStructure())) {
            /** @var FileTypeInterface $fileTypeService */
            $fileTypeService = $this->serviceManagerService->get($fileTypeDescriber->getServiceClassname());
            $this->explorerFileService->setFileMetas($fileTypeService, $path);
        }

        if (!$this->gibsonStoreService->hasFileImage($path)) {
            if ($fileTypeService === null) {
                $fileTypeService = $this->serviceManagerService->get($fileTypeDescriber->getServiceClassname());
            }

            try {
                $image = $fileTypeService->getImage($path);
                $checkSum = md5_file($path);

                $this->gibsonStoreService->setFileImage($path, $image, $checkSum ?: null);
            } catch (Exception $e) {
                // No Image
            }
        }
    }
}
