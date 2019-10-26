<?php
namespace GibsonOS\Module\Explorer\Factory\File;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Module\Explorer\Factory\File\Type\Describer;
use GibsonOS\Module\Explorer\Factory\File\Type\TypeInterface;
use GibsonOS\Module\Explorer\Service\File\Type\Describer\FileTypeDescriberInterface;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Core\Utility\File;
use OutOfBoundsException;

class Type
{
    /**
     * @param $filename
     * @return FileTypeInterface
     * @throws FileNotFound
     */
    public static function create($filename)
    {
        $fileTypeDescriber = Describer::create($filename);
        /** @var TypeInterface $className */
        $className = $fileTypeDescriber->getFactoryClassName();
        $fileType = $className::create($filename);

        return $fileType;
    }

    /**
     * @param $filename
     * @param FileTypeDescriberInterface $fileTypeDescriber
     * @return mixed
     * @throws FileNotFound
     */
    public static function createWithDescriber($filename, FileTypeDescriberInterface $fileTypeDescriber)
    {
        $fileEnding = File::getFileEnding($filename);

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