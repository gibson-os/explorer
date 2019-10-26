<?php
namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

use GibsonOS\Module\Explorer\Factory\File\Type\Image as ImageFactory;

class Image implements FileTypeDescriberInterface
{
    /**
     * @return string[]
     */
    public function getFileEndings():array
    {
        return [
            'bmp',
            'png',
            'gif'
        ];
    }

    /**
     * @return string[]
     */
    public function getMetasStructure(): array
    {
        return [
            'width',
            'height'
        ];
    }

    /**
     * @return string
     */
    public function getFactoryClassName(): string
    {
        return ImageFactory::class;
    }
}