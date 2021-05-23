<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type;

use Exception;
use GibsonOS\Core\Dto\Ffmpeg\Media;
use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\Ffmpeg\MediaService;

class AudioService implements FileTypeInterface
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
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
            'bitRate' => $media->getBitRate(),
            'audioStreams' => $this->getAudioStreams($media),
        ];
    }

    public function getImage(string $filename): Image
    {
        throw new Exception();
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
}
