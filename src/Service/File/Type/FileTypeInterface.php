<?php
namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Service\Image as ImageService;

interface FileTypeInterface
{
    /**
     * @param string $filename
     * @return array
     */
    public function getMetas($filename);

    /**
     * @param string $filename
     * @return ImageService|null
     */
    public function getImage($filename);
}