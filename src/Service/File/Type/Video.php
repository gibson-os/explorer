<?php
namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\NoVideoError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Service\Ffmpeg\Media as MediaService;
use GibsonOS\Core\Service\Image as ImageService;

class Video implements FileTypeInterface
{
    /**
     * @var MediaService
     */
    private $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * @param string $filename
     * @return ImageService
     * @throws DeleteError
     * @throws FileNotFound
     * @throws NoVideoError
     */
    public function getImage($filename)
    {
        $second = 180;

        if ($this->mediaService->getDuration() < 180) {
            $second = (int)($this->mediaService->getDuration()/3);

            if (
                $second === 0 &&
                $this->mediaService->getDuration() >= 1
            ) {
                $second = 1;
            }
        }

        return $this->mediaService->getImageBySecond($second);
    }

    /**
     * @param string $filename
     * @return array
     */
    public function getMetas($filename)
    {
        return [
            'duration' => str_replace(',', '.', $this->mediaService->getDuration()),
            'frameCount' => $this->mediaService->getFrames(),
            'bitRate' => $this->mediaService->getBitRate(),
            'videoStreams' => $this->getVideoStreams(),
            'audioStreams' => $this->getAudioStreams(),
            'subtitleStreams' => $this->getSubtitleStreams()
        ];
    }

    /**
     * @return array
     */
    private function getVideoStreams()
    {
        $videoStreams = [];

        foreach ($this->mediaService->getVideoStreams() as $streamId => $videoStream) {
            $videoStreams[$streamId] = [
                'width' => $videoStream->getWidth(),
                'height' => $videoStream->getHeight(),
                'fps' => $videoStream->getFps(),
                'codec' => $videoStream->getCodec(),
                'colorSpace' => $videoStream->getColorSpace(),
                'language' => $videoStream->getLanguage(),
                'default' => $videoStream->isDefault()
            ];
        }

        return $videoStreams;
    }

    /**
     * @return array
     */
    private function getAudioStreams()
    {
        $audioStreams = [];

        foreach ($this->mediaService->getAudioStreams() as $streamId => $audioStream) {
            $audioStreams[$streamId] = [
                'language' => $audioStream->getLanguage(),
                'channels' => $audioStream->getChannels(),
                'format' => $audioStream->getFormat(),
                'frequency' => $audioStream->getFrequency(),
                'bitrate' => $audioStream->getBitrate(),
                'default' => $audioStream->isDefault()
            ];
        }

        return $audioStreams;
    }

    /**
     * @return array
     */
    private function getSubtitleStreams()
    {
        $subtitleStreams = [];

        foreach ($this->mediaService->getSubtitleStreams() as $streamId => $subtitleStream) {
            $subtitleStreams[$streamId] = [
                'language' => $subtitleStream->getLanguage(),
                'default' => $subtitleStream->isDefault(),
                'forced' => $subtitleStream->isForced()
            ];
        }

        return $subtitleStreams;
    }
}