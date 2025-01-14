<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command\Html5;

use DateTime;
use Exception;
use GibsonOS\Core\Attribute\Command\Lock;
use GibsonOS\Core\Attribute\Command\Option;
use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Enum\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Module\Explorer\Exception\MediaException;
use GibsonOS\Module\Explorer\Repository\Html5\Media\PositionRepository;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use Psr\Log\LoggerInterface;
use ReflectionException;

/**
 * @description Delete converted files
 */
#[Cronjob(minutes: '5', seconds: '0', user: 'root')]
#[Lock('explorerHtml5DeleteCommand')]
class DeleteCommand extends AbstractCommand
{
    #[Option('Run dry. Files are not deleted')]
    private bool $dry = false;

    private string $mediaPath;

    public function __construct(
        private readonly MediaRepository $mediaRepository,
        private readonly PositionRepository $positionRepository,
        private readonly SettingRepository $settingRepository,
        private readonly DirService $dirService,
        private readonly FileService $fileService,
        private readonly ModelManager $modelManager,
        private readonly MediaService $mediaService,
        LoggerInterface $logger,
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws ClientException
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws JsonException
     * @throws MediaException
     * @throws ModelDeleteError
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    protected function run(): int
    {
        $this->mediaPath = $this->settingRepository->getByKeyAndModuleName(
            'explorer',
            0,
            'html5_media_path',
        )->getValue();

        $this->deleteWhereFileNotExists();
        $this->deleteWhereMediaNotExists();
        $this->deleteWhereLifetimeExpired();
        $this->deleteWhereSizeExceeded();

        return self::SUCCESS;
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws MediaException
     * @throws ModelDeleteError
     * @throws RecordException
     * @throws ReflectionException
     */
    private function deleteWhereFileNotExists(): void
    {
        foreach ($this->mediaRepository->getAllByStatus(ConvertStatus::GENERATED) as $media) {
            if (
                file_exists($media->getDir() . $media->getFilename())
                && (
                    !$media->isGenerationRequired()
                    || file_exists(
                        $this->mediaPath .
                        $media->getToken() .
                        $this->mediaService->getGeneratedFileEnding($media),
                    )
                )
            ) {
                $this->logger->debug(sprintf(
                    'Media %s exist. Not deleted.',
                    $this->mediaPath . $media->getToken(),
                ));

                continue;
            }

            $this->logger->info(sprintf(
                'Media %s deleted because file does not exist.',
                $this->mediaPath . $media->getToken(),
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
                    $this->mediaPath . $filename,
                ));
            } catch (SelectError) {
                try {
                    $this->logger->info(sprintf(
                        'Generated media %s deleted because media entity does not exist.',
                        $this->mediaPath . $filename,
                    ));

                    if (!$this->dry) {
                        $this->fileService->delete($file);
                    }
                } catch (FileNotFound) {
                    // File does not exist
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
                'html5_media_lifetime',
            )->getValue();

            if ($lifetime === '' || $lifetime === '0') {
                return;
            }

            foreach ($this->mediaRepository->getAllOlderThan(new DateTime('-' . $lifetime . ' days')) as $media) {
                $this->logger->info(sprintf(
                    'Media %s deleted because generate date %s is older than %s.' . PHP_EOL,
                    $media->getDir() . $media->getFilename(),
                    $media->getAdded()->format('Y-m-d'),
                    (new DateTime('-' . $lifetime . ' days'))->format('Y-m-d'),
                ));

                if (!$this->dry) {
                    $this->fileService->delete(
                        $this->mediaPath .
                        $media->getToken() .
                        $this->mediaService->getGeneratedFileEnding($media),
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
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    private function deleteWhereSizeExceeded(): void
    {
        try {
            $size = $this->settingRepository->getByKeyAndModuleName(
                'explorer',
                0,
                'html5_media_size',
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

            if ($size === 0) {
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
            $deleteSize = $this->deleteWhereSizeReached($deleteSize, $size, true);
            $this->deleteWhereSizeReached($deleteSize, $size, false);
        } catch (SelectError) {
            return;
        }
    }

    /**
     * @throws ClientException
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws JsonException
     * @throws MediaException
     * @throws ModelDeleteError
     * @throws RecordException
     * @throws ReflectionException
     */
    private function deleteWhereSizeReached(int $deleteSize, int $expectedSize, bool $onlyViewed): int
    {
        if ($deleteSize <= 0) {
            return $deleteSize;
        }

        foreach ($this->mediaRepository->getAllByStatus(ConvertStatus::GENERATED) as $media) {
            if ($deleteSize <= 0) {
                break;
            }

            if (!$media->isGenerationRequired()) {
                continue;
            }

            if ($media->isLocked()) {
                continue;
            }

            if ($onlyViewed && !$this->positionRepository->hasPosition($media->getId() ?? 0)) {
                continue;
            }

            $fileEnding = $this->mediaService->getGeneratedFileEnding($media);
            $fileSize = filesize($this->mediaPath . $media->getToken() . $fileEnding);
            $this->logger->info(sprintf(
                'Media %s deleted because folder size is %d Bytes bigger as %d Bytes.' . PHP_EOL,
                $media->getDir() . $media->getFilename(),
                $deleteSize,
                $expectedSize,
            ));

            if (!$this->dry) {
                $this->fileService->delete($this->mediaPath . $media->getToken() . $fileEnding);
                $this->modelManager->delete($media);
            }

            $deleteSize -= $fileSize;
        }

        return $deleteSize;
    }

    public function setDry(bool $dry): void
    {
        $this->dry = $dry;
    }
}
