<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Factory\AbstractSingletonFactory;
use GibsonOS\Core\Factory\ImageFactory;
use GibsonOS\Module\Explorer\Service\File\Type\JpgService;

class JpgFactory extends AbstractSingletonFactory implements TypeInterface
{
    /**
     * @return JpgService
     */
    public static function createInstance(): JpgService
    {
        $imageService = ImageFactory::create();

        return new JpgService($imageService);
    }

    public static function create(): JpgService
    {
        /** @var JpgService $service */
        $service = parent::create();

        return $service;
    }
}
