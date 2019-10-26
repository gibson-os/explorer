<?php
namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Module\Explorer\Service\File\Type\Image as ImageTypeService;
use GibsonOS\Core\Service\Image as ImageService;

class Image implements TypeInterface
{
    /**
     * @param string $filename
     * @return ImageTypeService
     * @throws FileNotFound
     */
    public static function create(string $filename): FileTypeInterface
    {
        $imageService = new ImageService();
        $imageService->load($filename);

        return new ImageTypeService($imageService);
    }
}