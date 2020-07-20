<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service;

use GibsonOS\Core\Service\DirService as CoreDirService;
use GibsonOS\Module\Explorer\Dto\Dir;

class DirService
{
    /**
     * @var GibsonStoreService
     */
    private $gibsonStoreService;

    /**
     * @var CoreDirService
     */
    private $coreDirService;

    public function __construct(GibsonStoreService $gibsonStoreService, CoreDirService $coreDirService)
    {
        $this->gibsonStoreService = $gibsonStoreService;
        $this->coreDirService = $coreDirService;
    }

    public function get(string $path): Dir
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $this->coreDirService->removeEndSlash($path));
        $name = array_pop($pathParts);

        return (new Dir(implode(DIRECTORY_SEPARATOR, $pathParts), $name))
            ->setSize((int) $this->gibsonStoreService->getDirMeta($path, 'dirsize'))
            ->setFiles((int) $this->gibsonStoreService->getDirMeta($path, 'filecount'))
            ->setDirs((int) $this->gibsonStoreService->getDirMeta($path, 'dircount'))
            ->setDirFiles((int) $this->gibsonStoreService->getDirMeta($path, 'dirfilecount'))
            ->setDirDirs((int) $this->gibsonStoreService->getDirMeta($path, 'dirdircount'))
            ->setIcon($this->gibsonStoreService->getDirMeta($path, 'icon'))
            ->setAccessed(fileatime($path))
            ->setModified(filemtime($path))
        ;
    }
}
