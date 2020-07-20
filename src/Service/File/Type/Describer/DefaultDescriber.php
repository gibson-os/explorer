<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

use GibsonOS\Module\Explorer\Service\File\Type\DefaultService;

class DefaultDescriber implements FileTypeDescriberInterface
{
    public function getFileEndings(): array
    {
        return [];
    }

    public function getMetasStructure(): array
    {
        return [];
    }

    public function getServiceClassname(): string
    {
        return DefaultService::class;
    }

    public function isImageAvailable(): bool
    {
        return false;
    }

    public function getCategory(): int
    {
        return 0;
    }
}
