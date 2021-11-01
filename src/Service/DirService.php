<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Service\DirService as CoreDirService;
use GibsonOS\Module\Explorer\Dto\Dir;

class DirService
{
    public function __construct(private GibsonStoreService $gibsonStoreService, private CoreDirService $coreDirService)
    {
    }

    /**
     * @throws GetError
     * @throws ReadError
     */
    public function get(string $path): Dir
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $this->coreDirService->removeEndSlash($path));
        $name = array_pop($pathParts);

        $dir = (new Dir(implode(DIRECTORY_SEPARATOR, $pathParts), $name))
            ->setAccessed(fileatime($path))
            ->setModified(filemtime($path))
        ;

        try {
            $dir
                ->setSize((int) $this->gibsonStoreService->getDirMeta($path, 'dirsize', 0))
                ->setFiles((int) $this->gibsonStoreService->getDirMeta($path, 'filecount', 0))
                ->setDirFiles((int) $this->gibsonStoreService->getDirMeta($path, 'dirfilecount', 0))
                ->setDirs((int) $this->gibsonStoreService->getDirMeta($path, 'dircount', 0))
                ->setDirDirs((int) $this->gibsonStoreService->getDirMeta($path, 'dirdircount', 0))
                ->setIcon($this->gibsonStoreService->getDirMeta($path, 'icon'))
            ;
        } catch (ExecuteError) {
            $size = 0;
            $files = 0;
            $dirs = 0;

            foreach ($this->coreDirService->getFiles($path) as $file) {
                if (is_dir($file)) {
                    ++$dirs;

                    continue;
                }

                ++$files;
                $size += filesize($file);
            }

            $dir
                ->setSize($size)
                ->setFiles($files)
                ->setDirFiles(0)
                ->setDirs($dirs)
                ->setDirDirs(0)
            ;
        }

        return $dir;
    }

    /**
     * @throws ExecuteError
     * @throws WriteError
     */
    public function set(Dir $dir): DirService
    {
        $dirPath = $this->coreDirService->addEndSlash($dir->getPath()) . $dir->getName();
        $this->gibsonStoreService
            ->setDirMeta($dirPath, 'dirsize', $dir->getSize())
            ->setDirMeta($dirPath, 'filecount', $dir->getFiles())
            ->setDirMeta($dirPath, 'dirfilecount', $dir->getDirFiles())
            ->setDirMeta($dirPath, 'dircount', $dir->getDirs())
            ->setDirMeta($dirPath, 'dirdircount', $dir->getDirDirs())
        ;

        if (!empty($dir->getIcon())) {
            $this->gibsonStoreService->setDirMeta($dirPath, 'icon', $dir->getIcon());
        }

        return $this;
    }
}
