<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Dto\Image;

interface FileTypeInterface
{
    public function getMetas(string $filename): array;

    public function getImage(string $filename): Image;
}
