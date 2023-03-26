<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type;

use Exception;
use GibsonOS\Core\Dto\Image;

class DefaultService implements FileTypeInterface
{
    public function getMetas(string $filename): array
    {
        return [];
    }

    public function getImage(string $filename): Image
    {
        throw new Exception();
    }
}
