<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command;

use Exception;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Flock\FlockError;
use GibsonOS\Core\Exception\Flock\UnFlockError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\EnvService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\FlockService;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Factory\File\TypeFactory;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;

class IndexerCommand extends AbstractCommand
{
    /**
     * @var FlockService
     */
    private $flockService;

    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * @var GibsonStoreService
     */
    private $gibsonStoreService;

    /**
     * @var DirService
     */
    private $dirService;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var EnvService
     */
    private $envService;

    public function __construct(
        FlockService $flockService,
        SettingRepository $settingRepository,
        GibsonStoreService $gibsonStoreService,
        DirService $dirService,
        FileService $fileService,
        EnvService $envService
    ) {
        $this->flockService = $flockService;
        $this->settingRepository = $settingRepository;
        $this->gibsonStoreService = $gibsonStoreService;
        $this->dirService = $dirService;
        $this->fileService = $fileService;
        $this->envService = $envService;

        $this->setOption('renew');
    }

    /**
     * @throws UnFlockError
     * @throws DateTimeError
     * @throws SelectError
     */
    protected function run(): int
    {
        try {
            $this->flockService->flock();

            $homePath = $this->settingRepository->getByKeyAndModuleName('explorer', 0, 'home_path');
            $this->indexDir($homePath->getValue(), false);

            $this->flockService->unFlock();
        } catch (FlockError $e) {
            // Indexer in progress
        }

        return 0;
    }

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
            } catch (FileNotFound $exception) {
                // File not found
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
     * @var int[]
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
     * @throws FileNotFound
     * @throws GetError
     * @throws ExecuteError
     * @throws ReadError
     * @throws WriteError
     */
    private function indexFile(string $path): void
    {
        $fileTypeDescriber = DescriberFactory::create($path);
        $fileTypeService = null;
        $checkSum = null;

        if (!$this->gibsonStoreService->hasFileMetas($path, $fileTypeDescriber->getMetasStructure())) {
            $fileTypeService = TypeFactory::create($path);
            $checkSum = md5_file($path);
            $this->gibsonStoreService->setFileMetas($path, $fileTypeService->getMetas($path), $checkSum ?: null);
        }

        if (!$this->gibsonStoreService->hasFileImage($path)) {
            if ($fileTypeService === null) {
                $fileTypeService = TypeFactory::create($path);
            }

            try {
                $image = $fileTypeService->getImage($path);

                if ($checkSum === null) {
                    $checkSum = md5_file($path);
                }

                $this->gibsonStoreService->setFileImage($path, $image, $checkSum ?: null);
            } catch (Exception $e) {
                // No Image
            }
        }
    }
}
