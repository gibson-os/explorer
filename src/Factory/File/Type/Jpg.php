<?php
namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Module\Explorer\Service\File\Type\Jpg as JpgTypeService;
use GibsonOS\Core\Service\Image as ImageService;

class Jpg implements TypeInterface
{
    /**
     * @param string $filename
     * @return JpgTypeService
     * @throws FileNotFound
     */
    public static function create(string $filename): FileTypeInterface
    {
        $imageService = new ImageService();
        $imageService->load($filename);

        return new JpgTypeService($imageService);
    }
}