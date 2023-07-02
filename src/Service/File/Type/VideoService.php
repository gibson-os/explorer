<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Dto\Ffmpeg\Media;
use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\Ffmpeg\NoVideoError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\Ffmpeg\MediaService;

class VideoService implements FileTypeInterface
{
    public function __construct(private MediaService $mediaService)
    {
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws LoadError
     * @throws NoVideoError
     * @throws ProcessError
     * @throws NoAudioError
     */
    public function getImage(string $filename): Image
    {
        $second = 180;
        $media = $this->mediaService->getMedia($filename);

        if ($media->getDuration() < 180) {
            $second = (int) ($media->getDuration() / 3);

            if (
                $second === 0
                && $media->getDuration() >= 1
            ) {
                $second = 1;
            }
        }

        return $this->mediaService->getImageBySecond($media, $second);
    }

    /**
     * @throws FileNotFound
     * @throws NoAudioError
     * @throws ProcessError
     */
    public function getMetas(string $filename): array
    {
        $media = $this->mediaService->getMedia($filename);

        return [
            'duration' => str_replace(',', '.', (string) $media->getDuration()),
            'frameCount' => $media->getFrames(),
            'bitRate' => $media->getBitRate(),
            'videoStreams' => $this->getVideoStreams($media),
            'audioStreams' => $this->getAudioStreams($media),
            'subtitleStreams' => $this->getSubtitleStreams($media),
        ];
    }

    private function getVideoStreams(Media $media): array
    {
        $videoStreams = [];

        foreach ($media->getVideoStreams() as $streamId => $videoStream) {
            $videoStreams[$streamId] = [
                'width' => $videoStream->getWidth(),
                'height' => $videoStream->getHeight(),
                'fps' => $videoStream->getFps(),
                'codec' => $videoStream->getCodec(),
                'colorSpace' => $videoStream->getColorSpace(),
                'language' => $videoStream->getLanguage(),
                'default' => $videoStream->isDefault(),
            ];
        }

        return $videoStreams;
    }

    private function getAudioStreams(Media $media): array
    {
        $audioStreams = [];

        foreach ($media->getAudioStreams() as $streamId => $audioStream) {
            $audioStreams[$streamId] = [
                'language' => $audioStream->getLanguage(),
                'channels' => $audioStream->getChannels(),
                'format' => $audioStream->getFormat(),
                'frequency' => $audioStream->getFrequency(),
                'bitrate' => $audioStream->getBitrate(),
                'default' => $audioStream->isDefault(),
            ];
        }

        return $audioStreams;
    }

    private function getSubtitleStreams(Media $media): array
    {
        $subtitleStreams = [];

        foreach ($media->getSubtitleStreams() as $streamId => $subtitleStream) {
            $subtitleStreams[$streamId] = [
                'language' => $subtitleStream->getLanguage(),
                'default' => $subtitleStream->isDefault(),
                'forced' => $subtitleStream->isForced(),
            ];
        }

        return $subtitleStreams;
    }
}
