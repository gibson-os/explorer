<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Factory\DirFactory;
use GibsonOS\Core\Factory\FileFactory;
use GibsonOS\Module\Explorer\Service\File\Type\Describer\FileTypeDescriberInterface;

class DescriberFactory
{
    /**
     * @throws \GibsonOS\Core\Exception\GetError
     * @throws FileNotFound
     */
    public static function create(string $filename): FileTypeDescriberInterface
    {
        $fileService = FileFactory::create();
        $dirService = DirFactory::create();

        $namespace = '\\GibsonOS\\Module\\Explorer\\Service\\File\\Type\\Describer\\';
        $classPath =
            realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            'Service' . DIRECTORY_SEPARATOR .
            'File' . DIRECTORY_SEPARATOR .
            'Type' . DIRECTORY_SEPARATOR .
            'Describer' . DIRECTORY_SEPARATOR;
        $fileEnding = $fileService->getFileEnding($filename);

        foreach ($dirService->getFiles($classPath, '*.php') as $path) {
            $classFilename = $fileService->getFilename($path);

            if (mb_strpos($classFilename, 'Interface') !== false) {
                continue;
            }

            $className = $namespace . str_replace('.php', '', $classFilename);
            $fileTypeDescriberService = new $className();

            if (!$fileTypeDescriberService instanceof FileTypeDescriberInterface) {
                continue;
            }

            if (in_array($fileEnding, $fileTypeDescriberService->getFileEndings())) {
                return $fileTypeDescriberService;
            }
        }

        throw new FileNotFound('Dateityp Beschreiber existiert nicht!');
    }
}