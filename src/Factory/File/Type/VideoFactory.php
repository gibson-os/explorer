<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Factory\AbstractSingletonFactory;
use GibsonOS\Core\Factory\Ffmpeg\MediaFactory;
use GibsonOS\Module\Explorer\Service\File\Type\VideoService;

class VideoFactory extends AbstractSingletonFactory implements TypeInterface
{
    /**
     * @throws GetError
     *
     * @return VideoService
     */
    public static function createInstance(): VideoService
    {
        $mediaService = MediaFactory::create();

        return new VideoService($mediaService);
    }

    public static function create(): VideoService
    {
        /** @var VideoService $service */
        $service = parent::create();

        return $service;
    }
}
