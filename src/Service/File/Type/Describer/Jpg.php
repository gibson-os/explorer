<?php
namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

use GibsonOS\Module\Explorer\Factory\File\Type\Jpg as JpgFactory;

class Jpg extends Image
{
    /**
     * @return string[]
     */
    public function getFileEndings(): array
    {
        return [
            'jpg',
            'jpeg'
        ];
    }

    /**
     * @return string
     */
    public function getFactoryClassName(): string
    {
        return JpgFactory::class;
    }
}