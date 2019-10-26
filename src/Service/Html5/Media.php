<?php
namespace GibsonOS\Module\Explorer\Service\Html5;

use DateTime;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Factory\Media as MediaFactory;
use GibsonOS\Core\Service\AbstractService;
use GibsonOS\Core\Service\Ffmpeg\Media as MediaService;
use GibsonOS\Module\Explorer\Model\Html5\Media as MediaModel;
use GibsonOS\Module\Explorer\Model\Html5\Media\Position as PositionModel;
use OutOfRangeException;

class Media extends AbstractService
{
    const STATUS_ERROR = 'error';
    const STATUS_WAIT = 'wait';
    const STATUS_GENERATE = 'generate';
    const STATUS_GENERATED = 'generated';

    /**
     * @var MediaModel
     */
    private $mediaModel;
    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * Media constructor.
     * @param MediaModel $mediaModel
     * @param MediaService $mediaService
     */
    public function __construct(MediaModel $mediaModel, MediaService $mediaService)
    {
        $this->mediaModel = $mediaModel;
        $this->mediaService = $mediaService;

        if ($mediaModel->getAudioStream() !== null) {
            $mediaService->selectAudioStream($mediaModel->getAudioStream());
        }
    }

    /**
     * @param string $filename
     * @throws DeleteError
     * @throws FileNotFound
     */
    public function convertToMp4(string $filename)
    {
        $this->mediaService->convert(
            $filename,
            'libx264',
            'libfdk_aac',
            [
                'crf' => 23,
                'preset' => 'veryfast',
                'movflags' => 'faststart'
            ]
        );
    }

    /**
     * @param string $filename
     * @throws DeleteError
     * @throws FileNotFound
     */
    public function convertToWebm(string $filename)
    {
        $this->mediaService->convert(
            $filename,
            'libvpx',
            'libvorbis',
            [
                'b:v' => '1500k',
                'q:a' => 4
            ]
        );
    }

    /**
     * @return array
     * @throws ConvertStatusError
     * @throws FileNotFound
     */
    public function getConvertStatus(): array
    {
        if ($this->mediaModel->getStatus() !== 'generate') {
            return ['status' => $this->mediaModel->getStatus()];
        }

        $media = MediaFactory::create($this->mediaModel->getDir() . $this->mediaModel->getFilename());
        $convertStatus = $media->getConvertStatus($this->mediaModel->getToken() . '.mp4');

        return [
            'frame' => $convertStatus->getFrame(),
            'frameCount' => $convertStatus->getFrames(),
            'percent' => $convertStatus->getPercent(),
            'fps' => $convertStatus->getFps(),
            'q' => $convertStatus->getQuality(),
            'size' => $convertStatus->getSize(),
            'time' => $convertStatus->getTime()->format('H:i:s'),
            'timeRemaining' => $convertStatus->getTimeRemaining()->format('H:i:s'),
            'bitrate' => $convertStatus->getBitrate(),
            'status' => $this->mediaModel->getStatus()
        ];
    }

    /**
     * @param int $currentPosition
     * @throws SaveError
     * @throws OutOfRangeException
     */
    public function savePosition(int $currentPosition)
    {
        if ($currentPosition === 0) {
            throw new OutOfRangeException('Position 0 ist nicht gültig!');
        }

        $position = new PositionModel();
        $position->setMediaId($this->mediaModel->getId());
        $position->setPosition($currentPosition);
        $position->setUserId(1); // @todo User id ermitteln
        $position->setModified(new DateTime());
        $position->save();
    }
}