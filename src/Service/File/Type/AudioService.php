<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Dto\Ffmpeg\Media;
use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Service\Ffmpeg\MediaService;
use GibsonOS\Core\Service\ImageService as CoreImageService;

class AudioService implements FileTypeInterface
{
    private MediaService $mediaService;

    private CoreImageService $imageService;

    public function __construct(MediaService $mediaService, CoreImageService $imageService)
    {
        $this->mediaService = $mediaService;
        $this->imageService = $imageService;
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
        return $this->imageService->load(
            realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'assets' . DIRECTORY_SEPARATOR .
            'img' . DIRECTORY_SEPARATOR .
            'audioFile.jpg'
        );
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
