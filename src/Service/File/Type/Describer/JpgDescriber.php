<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

use GibsonOS\Module\Explorer\Service\File\Type\JpgService;

class JpgDescriber extends ImageDescriber
{
    /**
     * @return string[]
     */
    public function getFileEndings(): array
    {
        return [
            'jpg',
            'jpeg',
        ];
    }

    public function getServiceClassname(): string
    {
        return JpgService::class;
    }
}
