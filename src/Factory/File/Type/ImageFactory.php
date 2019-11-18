<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Factory\ImageFactory as CoreImageFactory;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Module\Explorer\Service\File\Type\ImageService as ImageTypeService;

class ImageFactory implements TypeInterface
{
    /**
     * @return ImageTypeService
     */
    public static function create(): FileTypeInterface
    {
        $imageService = CoreImageFactory::create();

        return new ImageTypeService($imageService);
    }
}
