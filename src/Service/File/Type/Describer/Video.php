<?php
namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

use GibsonOS\Module\Explorer\Factory\File\Type\Video as VideoFactory;

class Video implements FileTypeDescriberInterface
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
            'webm'
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
            'subtitleStreams'
        ];
    }

    /**
     * @return string
     */
    public function getFactoryClassName(): string
    {
        return VideoFactory::class;
    }
}