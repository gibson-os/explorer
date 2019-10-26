<?php
namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Utility\Dir;
use GibsonOS\Core\Utility\File;
use GibsonOS\Module\Explorer\Service\File\Type\Describer\FileTypeDescriberInterface;

class Describer
{
    /**
     * @param $filename
     * @return FileTypeDescriberInterface
     * @throws FileNotFound
     */
    public static function create(string $filename): FileTypeDescriberInterface
    {
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
        $fileEnding = File::getFileEnding($filename);

        foreach (glob(Dir::escapeForGlob($classPath) . '*.php') as $path) {
            $classFilename = File::getFilename($path);

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