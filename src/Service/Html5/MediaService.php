<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\Html5;

use DateTime;
use GibsonOS\Core\Dto\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Service\AbstractService;
use GibsonOS\Core\Service\Ffmpeg\MediaService as CoreMediaService;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Model\Html5\Media\Position as PositionModel;
use OutOfRangeException;

class MediaService extends AbstractService
{
    /**
     * @var CoreMediaService
     */
    private $mediaService;

    /**
     * Media constructor.
     */
    public function __construct(CoreMediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ProcessError
     */
    public function convertToMp4(Media $media, string $filename)
    {
        $mediaDto = $this->mediaService->getMedia($media->getFilename());

        if (!empty($media->getAudioStream())) {
            $mediaDto->selectAudioStream($media->getAudioStream());
        }

        $this->mediaService->convert(
            $mediaDto,
            $filename,
            'libx264',
            'libfdk_aac',
            [
                'crf' => 23,
                'preset' => 'veryfast',
                'movflags' => 'faststart',
            ]
        );
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ProcessError
     */
    public function convertToWebm(Media $media, string $filename)
    {
        $mediaDto = $this->mediaService->getMedia($media->getFilename());

        if (!empty($media->getAudioStream())) {
            $mediaDto->selectAudioStream($media->getAudioStream());
        }

        $this->mediaService->convert(
            $mediaDto,
            $filename,
            'libvpx',
            'libvorbis',
            [
                'b:v' => '1500k',
                'q:a' => 4,
            ]
        );
    }

    /**
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws FileNotFound
     * @throws OpenError
     * @throws ProcessError
     * @throws SetError
     */
    public function getConvertStatus(Media $media): ConvertStatus
    {
        if ($media->getStatus() !== 'generate') {
            return new ConvertStatus($media->getStatus());
        }

        $mediaDto = $this->mediaService->getMedia($media->getFilename());
        $convertStatus = $this->mediaService->getConvertStatus($mediaDto, $media->getToken() . '.mp4')
            ->setStatus($media->getStatus())
        ;

        return $convertStatus;
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SaveError
     */
    public function savePosition(Media $media, int $currentPosition)
    {
        if ($currentPosition === 0) {
            throw new OutOfRangeException('Position 0 ist nicht gültig!');
        }

        $position = new PositionModel();
        $position->setMediaId($media->getId());
        $position->setPosition($currentPosition);
        $position->setUserId(1); // @todo User id ermitteln
        $position->setModified(new DateTime());
        $position->save();
    }
}
