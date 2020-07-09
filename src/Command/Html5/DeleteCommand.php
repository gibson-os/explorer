<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command\Html5;

use DateTime;
use Exception;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\ArgumentError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Flock\FlockError;
use GibsonOS\Core\Exception\Flock\UnFlockError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\FlockService;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;

class DeleteCommand extends AbstractCommand
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * @var FlockService
     */
    private $flockService;

    /**
     * @var DirService
     */
    private $dirService;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var string
     */
    private $mediaPath;

    public function __construct(
        MediaRepository $mediaRepository,
        SettingRepository $settingRepository,
        FlockService $flockService,
        DirService $dirService,
        FileService $fileService
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->settingRepository = $settingRepository;
        $this->flockService = $flockService;
        $this->dirService = $dirService;
        $this->fileService = $fileService;

        $this->setOption('dry');
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ModelDeleteError
     * @throws SelectError
     * @throws ArgumentError
     * @throws UnFlockError
     */
    protected function run(): int
    {
        try {
            $this->flockService->flock();

            $this->mediaPath = $this->settingRepository->getByKeyAndModuleName(
                'explorer',
                0,
                'html5_media_path'
            )->getValue();

            $this->deleteWhereFileNotExists();
            $this->deleteWhereMediaNotExists();
            $this->deleteWhereLifetimeExpired();
            $this->deleteWhereSizeExceeded();

            $this->flockService->unFlock();
        } catch (FlockError $e) {
            // Delete in progress
        }

        return 0;
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     * @throws ModelDeleteError
     * @throws ArgumentError
     */
    private function deleteWhereFileNotExists(): void
    {
        foreach ($this->mediaRepository->getAllByStatus(Media::STATUS_GENERATED) as $media) {
            if (file_exists($media->getDir() . $media->getFilename())) {
                continue;
            }

            if ($this->hasOption('dry')) {
                printf(
                    'Media %s deleted because file does not exist.' . PHP_EOL,
                    $media->getDir() . $media->getFilename()
                );
            } else {
                $media->delete();
            }
        }
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws GetError
     * @throws ArgumentError
     */
    private function deleteWhereMediaNotExists(): void
    {
        foreach ($this->dirService->getFiles($this->mediaPath) as $file) {
            $filename = $this->fileService->getFilename($file);
            $token = mb_substr($filename, 0, (int) mb_strpos($filename, '.'));

            try {
                $this->mediaRepository->getByToken($token);
            } catch (SelectError $e) {
                try {
                    if ($this->hasOption('dry')) {
                        printf(
                            'Generated Video %s deleted because media entity does not exist.' . PHP_EOL,
                            $this->mediaPath . $filename
                        );
                    } else {
                        $this->fileService->delete($this->mediaPath, $filename);
                    }
                } catch (FileNotFound $e) {
                    // File does not exists
                }
            }
        }
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ModelDeleteError
     * @throws ArgumentError
     * @throws Exception
     */
    private function deleteWhereLifetimeExpired(): void
    {
        try {
            $lifetime = $this->settingRepository->getByKeyAndModuleName(
                'explorer',
                0,
                'html5_media_lifetime'
            )->getValue();

            if (empty($lifetime)) {
                return;
            }

            foreach ($this->mediaRepository->getAllOlderThan(new DateTime('-' . $lifetime . ' days')) as $media) {
                if ($this->hasOption('dry')) {
                    printf(
                        'Media %s deleted because generate date %s is older than %s.' . PHP_EOL,
                        $media->getDir() . $media->getFilename(),
                        $media->getAdded()->format('Y-m-d'),
                        (new DateTime('-' . $lifetime . ' days'))->format('Y-m-d')
                    );
                } else {
                    $this->fileService->delete($this->dirService->addEndSlash($media->getDir()) . $media->getFilename());
                    $media->delete();
                }
            }
        } catch (SelectError $e) {
            return;
        }
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ArgumentError
     * @throws ModelDeleteError
     */
    private function deleteWhereSizeExceeded(): void
    {
        try {
            $size = $this->settingRepository->getByKeyAndModuleName(
                'explorer',
                0,
                'html5_media_size'
            )->getValue();

            $hits = [];
            preg_match('/(\d+)(\w*)/', $size, $hits);
            $size = (int) $hits[1];

            if (array_key_exists(2, $hits)) {
                $types = [
                    'k' => 1024,
                    'kb' => 1024,
                    'm' => 1048576,
                    'mb' => 1048576,
                    'g' => 1073741824,
                    'gb' => 1073741824,
                ];

                $size *= $types[$hits[2]];
            }

            if (empty($size)) {
                return;
            }

            $dirSize = 0;

            foreach ($this->dirService->getFiles($this->mediaPath) as $file) {
                $dirSize += filesize($file);
            }

            if ($dirSize <= $size) {
                return;
            }

            $deleteSize = $dirSize - $size;

            foreach ($this->mediaRepository->getAllByStatus('generated') as $media) {
                if ($deleteSize <= 0) {
                    break;
                }

                $fileSize = filesize($this->mediaPath . $media->getToken() . '.mp4');

                if ($this->hasOption('dry')) {
                    printf(
                        'Media %s deleted because folder size is %d Bytes bigger as %d Bytes.' . PHP_EOL,
                        $media->getDir() . $media->getFilename(),
                        $deleteSize,
                        $size
                    );
                } else {
                    $this->fileService->delete($this->dirService->addEndSlash($media->getDir()) . $media->getFilename());
                    $media->delete();
                }

                $deleteSize -= $fileSize;
            }
        } catch (SelectError $e) {
            return;
        }
    }
}
