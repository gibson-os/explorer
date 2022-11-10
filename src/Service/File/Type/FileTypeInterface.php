<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Dto\Image;

interface FileTypeInterface
{
    /**
     * @throws \Exception
     */
    public function getMetas(string $filename): array;

    /**
     * @throws \Exception
     */
    public function getImage(string $filename): Image;
}
