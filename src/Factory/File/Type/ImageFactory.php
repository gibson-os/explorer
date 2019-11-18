<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Factory\AbstractSingletonFactory;
use GibsonOS\Core\Factory\ImageFactory as CoreImageFactory;
use GibsonOS\Module\Explorer\Service\File\Type\ImageService;

class ImageFactory extends AbstractSingletonFactory implements TypeInterface
{
    /**
     * @return ImageService
     */
    public static function createInstance(): ImageService
    {
        $imageService = CoreImageFactory::create();

        return new ImageService($imageService);
    }

    public static function create(): ImageService
    {
        /** @var ImageService $service */
        $service = parent::create();

        return $service;
    }
}
