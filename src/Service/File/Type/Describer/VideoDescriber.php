<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

use GibsonOS\Module\Explorer\Service\File\Type\VideoService;

class VideoDescriber implements FileTypeDescriberInterface
{
    /**
     * @return string[]
     */
    public function getFileEndings(): array
    {
        return [
            'asf',
            'avi',
            'mkv',
            'mpg',
            'mpeg',
            'ogg',
            'fla',
            'swf',
            'flv',
            'f4v',
            'f4p',
            'mp4',
            'mov',
            '3gp',
            'wmv',
            'rm',
            'webm',
        ];
    }

    /**
     * @return string[]
     */
    public function getMetasStructure(): array
    {
        return [
            'duration',
            'frameCount',
            'bitRate',
            'videoStreams',
            'audioStreams',
            'subtitleStreams',
        ];
    }

    public function getServiceClassname(): string
    {
        return VideoService::class;
    }

    public function isImageAvailable(): bool
    {
        return true;
    }

    public function getCategory(): int
    {
        return self::CATEGORY_VIDEO;
    }
}
