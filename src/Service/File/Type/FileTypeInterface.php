<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Dto\Image;

interface FileTypeInterface
{
    /**
     * @param string $filename
     *
     * @return array
     */
    public function getMetas(string $filename): array;

    /**
     * @param string $filename
     *
     * @return Image
     */
    public function getImage(string $filename): Image;
}
