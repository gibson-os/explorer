<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;

interface TypeInterface
{
    /**
     * @return FileTypeInterface
     */
    public static function create(): FileTypeInterface;
}
