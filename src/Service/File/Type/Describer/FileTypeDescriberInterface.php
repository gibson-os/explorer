<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type\Describer;

interface FileTypeDescriberInterface
{
    public const CATEGORY_IMAGE = 1;

    public const CATEGORY_VIDEO = 2;

    public const CATEGORY_PDF = 3;

    public const CATEGORY_AUDIO = 4;

    public const CATEGORY_OFFICE = 5;

    public const CATEGORY_ARCHIVE = 6;

    public const CATEGORY_BINARY = 7;

    public const CATEGORY_TEXT = 8;

    /**
     * @return string[]
     */
    public function getFileEndings(): array;

    /**
     * @return string[]
     */
    public function getMetasStructure(): array;

    /**
     * @return class-string
     */
    public function getServiceClassname(): string;

    public function isImageAvailable(): bool;

    public function getCategory(): int;
}
