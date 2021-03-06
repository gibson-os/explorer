<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command\Html5;

use DateTime;
use Exception;
use GibsonOS\Core\Attribute\Command\Option;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Flock\LockError;
use GibsonOS\Core\Exception\Flock\UnlockError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use Psr\Log\LoggerInterface;

/**
 * @description Delete converted files
 */
#[Cronjob(minutes: '5', seconds: '0', user: 'root')]
class DeleteCommand extends AbstractCommand
{
    #[Option('Run dry. Files are not deleted')]
    private bool $dry = false;

    private string $mediaPath;

    public function __construct(
        private MediaRepository $mediaRepository,
        private SettingRepository $settingRepository,
        private LockService $lockService,
        private DirService $dirService,
        private FileService $fileService,
        private ModelManager $modelManager,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ModelDeleteError
     * @throws SelectError
     * @throws UnlockError
     */
    protected function run(): int
    {
        try {
            $this->lockService->lock();

            $this->mediaPath = $this->settingRepository->getByKeyAndModuleName(
                'explorer',
                0,
                'html5_media_path'
            )->getValue();

            $this->deleteWhereFileNotExists();
            $this->deleteWhereMediaNotExists();
            $this->deleteWhereLifetimeExpired();
            $this->deleteWhereSizeExceeded();

            $this->lockService->unlock();
        } catch (LockError) {
            // Delete in progress
        }

        return self::SUCCESS;
    }

    /**
     * @throws SelectError
     * @throws ModelDeleteError
     */
    private function deleteWhereFileNotExists(): void
    {
        foreach ($this->mediaRepository->getAllByStatus(Media::STATUS_GENERATED) as $media) {
            if (file_exists($media->getDir() . $media->getFilename())) {
                continue;
            }

            if ($this->dry) {
                printf(
                    'Media %s deleted because file does not exist.' . PHP_EOL,
                    $media->getDir() . $media->getFilename()
                );
            } else {
                $this->modelManager->delete($media);
            }
        }
    }

    /**
     * @throws DeleteError
     * @throws GetError
     */
    private function deleteWhereMediaNotExists(): void
    {
        foreach ($this->dirService->getFiles($this->mediaPath) as $file) {
            $filename = $this->fileService->getFilename($file);
            $token = mb_substr($filename, 0, (int) mb_strpos($filename, '.'));

            try {
                $this->mediaRepository->getByToken($token);
            } catch (SelectError) {
                try {
                    if ($this->dry) {
                        printf(
                            'Generated Video %s deleted because media entity does not exist.' . PHP_EOL,
                            $this->mediaPath . $filename
                        );
                    } else {
                        $this->fileService->delete($file);
                    }
                } catch (FileNotFound) {
                    // File does not exists
                }
            }
        }
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ModelDeleteError
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
                if ($this->dry) {
                    printf(
                        'Media %s deleted because generate date %s is older than %s.' . PHP_EOL,
                        $media->getDir() . $media->getFilename(),
                        $media->getAdded()->format('Y-m-d'),
                        (new DateTime('-' . $lifetime . ' days'))->format('Y-m-d')
                    );
                } else {
                    $this->fileService->delete($this->mediaPath . $media->getToken() . '.mp4');
                    $this->modelManager->delete($media);
                }
            }
        } catch (SelectError) {
            return;
        }
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
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

                if ($this->dry) {
                    printf(
                        'Media %s deleted because folder size is %d Bytes bigger as %d Bytes.' . PHP_EOL,
                        $media->getDir() . $media->getFilename(),
                        $deleteSize,
                        $size
                    );
                } else {
                    $this->fileService->delete($this->mediaPath . $media->getToken() . '.mp4');
                    $this->modelManager->delete($media);
                }

                $deleteSize -= $fileSize;
            }
        } catch (SelectError) {
            return;
        }
    }

    public function setDry(bool $dry): void
    {
        $this->dry = $dry;
    }
}
