<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

use GibsonOS\Module\Explorer\Service\File\Type\AudioService;

class AudioDescriber implements FileTypeDescriberInterface
{
    public function getFileEndings(): array
    {
        return [
            'wav',
            'mp3',
            'm4a',
            'f4a',
            'f4b',
            'aiff',
        ];
    }

    public function getMetasStructure(): array
    {
        return [];
    }

    public function getServiceClassname(): string
    {
        return AudioService::class;
    }

    public function isImageAvailable(): bool
    {
        return false;
    }

    public function getCategory(): int
    {
        return self::CATEGORY_AUDIO;
    }
}
