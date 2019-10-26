<?php
namespace GibsonOS\Module\Explorer\Service\File\Type;

class Jpg extends Image
{
    /**
     * @param string $filename
     * @return array
     */
    public function getMetas($filename)
    {
        return array_merge(parent::getMetas($filename), exif_read_data($filename));
    }
}