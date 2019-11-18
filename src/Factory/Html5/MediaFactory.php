<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\Html5;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Factory\Ffmpeg\MediaFactory as CoreMediaFactory;
use GibsonOS\Module\Explorer\Service\Html5\MediaService as MediaService;

class MediaFactory
{
    /**
     * @throws GetError
     *
     * @return MediaService
     */
    public static function create()
    {
        $mediaService = CoreMediaFactory::create();

        return new MediaService($mediaService);
    }
}
