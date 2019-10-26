<?php
namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Factory\Media as MediaFactory;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Module\Explorer\Service\File\Type\Video as VideoTypeService;

class Video implements TypeInterface
{
    /**
     * @param string $filename
     * @return VideoTypeService
     * @throws FileNotFound
     */
    public static function create(string $filename): FileTypeInterface
    {
        $mediaService = MediaFactory::create($filename);

        return new VideoTypeService($mediaService);
    }
}