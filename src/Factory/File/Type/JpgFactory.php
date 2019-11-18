<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Factory\ImageFactory;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Module\Explorer\Service\File\Type\JpgService;

class JpgFactory implements TypeInterface
{
    /**
     * @return JpgService
     */
    public static function create(): FileTypeInterface
    {
        $imageService = ImageFactory::create();

        return new JpgService($imageService);
    }
}
