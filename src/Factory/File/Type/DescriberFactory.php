<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File\Type;

use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Module\Explorer\Service\File\Type\Describer\DefaultDescriber;
use GibsonOS\Module\Explorer\Service\File\Type\Describer\FileTypeDescriberInterface;

class DescriberFactory
{
    public function __construct(private ServiceManagerService $serviceManagerService)
    {
    }

    /**
     * @throws FactoryError
     */
    public function create(string $filename): FileTypeDescriberInterface
    {
        $fileService = $this->serviceManagerService->get(FileService::class);
        $dirService = $this->serviceManagerService->get(DirService::class);

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

            /** @var class-string $className */
            $className = $namespace . str_replace('.php', '', $classFilename);
            $fileTypeDescriberService = $this->serviceManagerService->get($className);

            if (!$fileTypeDescriberService instanceof FileTypeDescriberInterface) {
                continue;
            }

            if (in_array($fileEnding, $fileTypeDescriberService->getFileEndings())) {
                return $fileTypeDescriberService;
            }
        }

        return new DefaultDescriber();
    }
}
