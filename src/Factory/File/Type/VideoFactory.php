<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Factory\Ffmpeg\MediaFactory;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Module\Explorer\Service\File\Type\VideoService as VideoTypeService;

class VideoFactory implements TypeInterface
{
    /**
     * @throws GetError
     *
     * @return VideoTypeService
     */
    public static function create(): FileTypeInterface
    {
        $mediaService = MediaFactory::create();

        return new VideoTypeService($mediaService);
    }
}
