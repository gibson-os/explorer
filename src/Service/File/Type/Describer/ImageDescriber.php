<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

use GibsonOS\Module\Explorer\Service\File\Type\ImageService;

class ImageDescriber implements FileTypeDescriberInterface
{
    /**
     * @return string[]
     */
    public function getFileEndings(): array
    {
        return [
            'bmp',
            'png',
            'gif',
        ];
    }

    /**
     * @return string[]
     */
    public function getMetasStructure(): array
    {
        return [
            'width',
            'height',
        ];
    }

    public function getServiceClassname(): string
    {
        return ImageService::class;
    }

    public function isImageAvailable(): bool
    {
        return true;
    }

    public function getCategory(): int
    {
        return self::CATEGORY_IMAGE;
    }
}
