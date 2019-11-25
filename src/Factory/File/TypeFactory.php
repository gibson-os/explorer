<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Factory\File\Type\TypeInterface;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;

class TypeFactory
{
    /**
     * @throws GetError
     * @throws FileNotFound
     */
    public static function create(string $filename): FileTypeInterface
    {
        $fileTypeDescriber = DescriberFactory::create($filename);
        /** @var TypeInterface $className */
        $className = $fileTypeDescriber->getFactoryClassName();
        $fileType = $className::create();

        return $fileType;
    }
}
