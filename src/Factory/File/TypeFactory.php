<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Factory\File\Type\TypeInterface;
use GibsonOS\Module\Explorer\Service\File\Type\Describer\FileTypeDescriberInterface;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use OutOfBoundsException;

class TypeFactory
{
    /**
     * @param string $filename
     *
     * @throws GetError
     * @throws FileNotFound
     *
     * @return FileTypeInterface
     */
    public static function create(string $filename): FileTypeInterface
    {
        $fileTypeDescriber = DescriberFactory::create($filename);
        /** @var TypeInterface $className */
        $className = $fileTypeDescriber->getFactoryClassName();
        $fileType = $className::create();

        return $fileType;
    }

    /**
     * @param string                     $filename
     * @param FileTypeDescriberInterface $fileTypeDescriber
     *
     * @throws FileNotFound
     *
     * @return mixed
     */
    public static function createWithDescriber(string $filename, FileTypeDescriberInterface $fileTypeDescriber)
    {
        $fileService = FileFactory::create();
        $fileEnding = $fileService->getFileEnding($filename);

        if (!in_array($fileEnding, $fileTypeDescriber->getFileEndings())) {
            throw new OutOfBoundsException('Datei passt nicht zum Describer!');
        }

        $className = str_replace(
            '\\Service\\File\\Type\\Describer\\',
            '\\Factory\\File\\Type\\',
            get_class($fileTypeDescriber)
        );

        $fileTypeService = $className::create($filename);

        if (!$fileTypeService instanceof FileTypeInterface) {
            throw new FileNotFound('Dateityp Service ist keine Instanz vom interface!');
        }

        return $fileTypeService;
    }
}
