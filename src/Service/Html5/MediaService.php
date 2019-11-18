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
     *
     * @param CoreMediaService $mediaService
     */
    public function __construct(CoreMediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * @param string $filename
     *
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ProcessError
     */
    public function convertToMp4(string $filename)
    {
        $this->mediaService->convert(
            $this->mediaService->getMedia($filename),
            'libx264',
            'libfdk_aac',
            null,
            [
                'crf' => 23,
                'preset' => 'veryfast',
                'movflags' => 'faststart',
            ]
        );
    }

    /**
     * @param string $filename
     *
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ProcessError
     */
    public function convertToWebm(string $filename)
    {
        $this->mediaService->convert(
            $this->mediaService->getMedia($filename),
            'libvpx',
            'libvorbis',
            null,
            [
                'b:v' => '1500k',
                'q:a' => 4,
            ]
        );
    }

    /**
     * @param Media $media
     *
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws FileNotFound
     * @throws OpenError
     * @throws ProcessError
     * @throws SetError
     *
     * @return ConvertStatus
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
     * @param Media $media
     * @param int   $currentPosition
     *
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
