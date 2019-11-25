<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

use GibsonOS\Module\Explorer\Factory\File\Type\ImageFactory as ImageFactory;

class ImageService implements FileTypeDescriberInterface
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

    public function getFactoryClassName(): string
    {
        return ImageFactory::class;
    }
}
