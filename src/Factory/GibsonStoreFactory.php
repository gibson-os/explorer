<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory;

use GibsonOS\Core\Factory\AbstractSingletonFactory;
use GibsonOS\Core\Factory\DirFactory;
use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Core\Factory\Image\ThumbnailFactory;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;

class GibsonStoreFactory extends AbstractSingletonFactory
{
    protected static function createInstance(): GibsonStoreService
    {
        return new GibsonStoreService(
            FileFactory::create(),
            DirFactory::create(),
            ThumbnailFactory::create()
        );
    }

    public static function create(): GibsonStoreService
    {
        /** @var GibsonStoreService $service */
        $service = parent::create();

        return $service;
    }
}
