<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command\Html5;

use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Flock\FlockError;
use GibsonOS\Core\Exception\Flock\UnFlockError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\FlockService;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;

class ConvertCommand extends AbstractCommand
{
    private const FLOCK_NAME = 'html5Convert';

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * @var FlockService
     */
    private $flockService;

    public function __construct(
        MediaRepository $mediaRepository,
        MediaService $mediaService,
        SettingRepository $settingRepository,
        FlockService $flockService
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->mediaService = $mediaService;
        $this->settingRepository = $settingRepository;
        $this->flockService = $flockService;
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws GetError
     * @throws ProcessError
     * @throws SaveError
     * @throws SelectError
     * @throws UnFlockError
     */
    protected function run(): int
    {
        try {
            $this->flockService->flock(self::FLOCK_NAME);

            foreach ($this->mediaRepository->getAllByStatus(Media::STATUS_WAIT) as $media) {
                $media
                    ->setStatus(Media::STATUS_GENERATE)
                    ->save()
                ;

                try {
                    $this->mediaService->convertToMp4(
                        $media,
                        $this->settingRepository->getByKeyAndModuleName(
                            'explorer',
                            0,
                            'html5_media_path'
                        )->getValue() . $media->getToken() . '.mp4'
                    );
                    $media->setStatus(Media::STATUS_GENERATED);
                } catch (FileNotFound $e) {
                    $media->setStatus(Media::STATUS_ERROR);
                }

                $media->save();

                // @todo c2dm muss noch rein
            }

            $this->flockService->unFlock(self::FLOCK_NAME);
        } catch (FlockError $e) {
            // Convert in progress
        }

        return 0;
    }
}
