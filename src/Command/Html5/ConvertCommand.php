<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command\Html5;

use GibsonOS\Core\Attribute\Install\Cronjob;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Flock\LockError;
use GibsonOS\Core\Exception\Flock\UnlockError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\File\Type\Describer\FileTypeDescriberInterface;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use JsonException;
use Psr\Log\LoggerInterface;
use ReflectionException;

/**
 * @description Convert queued medias
 */
#[Cronjob(user: 'root')]
class ConvertCommand extends AbstractCommand
{
    private const LOCK_NAME = 'html5Convert';

    public function __construct(
        private MediaRepository $mediaRepository,
        private MediaService $mediaService,
        private SettingRepository $settingRepository,
        private LockService $lockService,
        private ModelManager $modelManager,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws DeleteError
     * @throws GetError
     * @throws NoAudioError
     * @throws ProcessError
     * @throws SaveError
     * @throws SelectError
     * @throws UnlockError
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function run(): int
    {
        try {
            $this->lockService->lock(self::LOCK_NAME);

            foreach ($this->mediaRepository->getAllByStatus(Media::STATUS_WAIT) as $media) {
                $this->modelManager->save($media->setStatus(Media::STATUS_GENERATE));

                try {
                    $filename = $this->settingRepository->getByKeyAndModuleName(
                        'explorer',
                        0,
                        'html5_media_path'
                    )->getValue() . $media->getToken() . '.';

                    switch ($media->getType()) {
                        case FileTypeDescriberInterface::CATEGORY_VIDEO:
                            $this->mediaService->convertToMp4($media, $filename . 'mp4');

                            break;
                        case FileTypeDescriberInterface::CATEGORY_AUDIO:
                            $this->mediaService->convertToMp3($media, $filename . 'mp3');

                            break;
                    }

                    $media->setStatus(Media::STATUS_GENERATED);
                } catch (FileNotFound) {
                    $media->setStatus(Media::STATUS_ERROR);
                }

                $this->modelManager->save($media);

                // @todo c2dm muss noch rein
            }

            $this->lockService->unlock(self::LOCK_NAME);
        } catch (LockError) {
            // Convert in progress
        }

        return self::SUCCESS;
    }
}
