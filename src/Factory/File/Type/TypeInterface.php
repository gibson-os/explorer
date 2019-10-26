<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;

interface TypeInterface
{
    /**
     * @param string $filename
     * @return FileTypeInterface
     */
    public static function create(string $filename): FileTypeInterface;
}