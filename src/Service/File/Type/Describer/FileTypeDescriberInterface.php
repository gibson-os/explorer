<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

interface FileTypeDescriberInterface
{
    /**
     * @return string[]
     */
    public function getFileEndings(): array;

    /**
     * @return string[]
     */
    public function getMetasStructure(): array;

    /**
     * @return string
     */
    public function getFactoryClassName(): string;
}
