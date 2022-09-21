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
use GibsonOS\Module\Explorer\Exception\MediaException;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use JsonException;
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
        private readonly MediaRepository $mediaRepository,
        private readonly SettingRepository $settingRepository,
        private readonly LockService $lockService,
        private readonly DirService $dirService,
        private readonly FileService $fileService,
        private readonly ModelManager $modelManager,
        private readonly MediaService $mediaService,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws JsonException
     * @throws MediaException
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
     * @throws ModelDeleteError
     * @throws SelectError
     * @throws MediaException
     * @throws JsonException
     */
    private function deleteWhereFileNotExists(): void
    {
        foreach ($this->mediaRepository->getAllByStatus(Media::STATUS_GENERATED) as $media) {
            if (
                file_exists($media->getDir() . $media->getFilename()) &&
                (
                    !$media->isGenerationRequired() ||
                    file_exists(
                        $this->mediaPath .
                        $media->getToken() .
                        $this->mediaService->getGeneratedFileEnding($media)
                    )
                )
            ) {
                $this->logger->debug(sprintf(
                    'Media %s exist. Not deleted.',
                    $this->mediaPath . $media->getToken()
                ));

                continue;
            }

            $this->logger->info(sprintf(
                'Media %s deleted because file does not exist.',
                $this->mediaPath . $media->getToken()
            ));

            if (!$this->dry) {
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

                $this->logger->debug(sprintf(
                    'Generated media %s exist. Not deleted.',
                    $this->mediaPath . $filename
                ));
            } catch (SelectError) {
                try {
                    $this->logger->info(sprintf(
                        'Generated media %s deleted because media entity does not exist.',
                        $this->mediaPath . $filename
                    ));

                    if (!$this->dry) {
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
                $this->logger->info(sprintf(
                    'Media %s deleted because generate date %s is older than %s.' . PHP_EOL,
                    $media->getDir() . $media->getFilename(),
                    $media->getAdded()->format('Y-m-d'),
                    (new DateTime('-' . $lifetime . ' days'))->format('Y-m-d')
                ));

                if (!$this->dry) {
                    $this->fileService->delete(
                        $this->mediaPath .
                        $media->getToken() .
                        $this->mediaService->getGeneratedFileEnding($media)
                    );
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
     * @throws JsonException
     * @throws MediaException
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

                if (!$media->isGenerationRequired()) {
                    continue;
                }

                $fileEnding = $this->mediaService->getGeneratedFileEnding($media);
                $fileSize = filesize($this->mediaPath . $media->getToken() . $fileEnding);
                $this->logger->info(sprintf(
                    'Media %s deleted because folder size is %d Bytes bigger as %d Bytes.' . PHP_EOL,
                    $media->getDir() . $media->getFilename(),
                    $deleteSize,
                    $size
                ));

                if (!$this->dry) {
                    $this->fileService->delete($this->mediaPath . $media->getToken() . $fileEnding);
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
