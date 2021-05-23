<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command\Html5;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Flock\LockError;
use GibsonOS\Core\Exception\Flock\UnlockError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\LockService;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\File\Type\Describer\FileTypeDescriberInterface;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use Psr\Log\LoggerInterface;

class ConvertCommand extends AbstractCommand
{
    private const LOCK_NAME = 'html5Convert';

    private MediaRepository $mediaRepository;

    private MediaService $mediaService;

    private SettingRepository $settingRepository;

    private LockService $lockService;

    public function __construct(
        MediaRepository $mediaRepository,
        MediaService $mediaService,
        SettingRepository $settingRepository,
        LockService $lockService,
        LoggerInterface $logger
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->mediaService = $mediaService;
        $this->settingRepository = $settingRepository;
        $this->lockService = $lockService;

        parent::__construct($logger);
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws GetError
     * @throws ProcessError
     * @throws SaveError
     * @throws SelectError
     * @throws UnlockError
     * @throws NoAudioError
     */
    protected function run(): int
    {
        try {
            $this->lockService->lock(self::LOCK_NAME);

            foreach ($this->mediaRepository->getAllByStatus(Media::STATUS_WAIT) as $media) {
                $media
                    ->setStatus(Media::STATUS_GENERATE)
                    ->save()
                ;

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
                } catch (FileNotFound $e) {
                    $media->setStatus(Media::STATUS_ERROR);
                }

                $media->save();

                // @todo c2dm muss noch rein
            }

            $this->lockService->unlock(self::LOCK_NAME);
        } catch (LockError $e) {
            // Convert in progress
        }

        return 0;
    }
}
