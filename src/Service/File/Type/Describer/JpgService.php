<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

use GibsonOS\Module\Explorer\Factory\File\Type\JpgFactory as JpgFactory;

class JpgService extends ImageService
{
    /**
     * @return string[]
     */
    public function getFileEndings(): array
    {
        return [
            'jpg',
            'jpeg',
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
