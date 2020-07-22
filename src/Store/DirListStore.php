<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store;

use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Store\AbstractStore;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;

class DirListStore extends AbstractStore
{
    /**
     * @var string
     */
    private $homePath = DIRECTORY_SEPARATOR;

    /**
     * @var string
     */
    private $dir = DIRECTORY_SEPARATOR;

    /**
     * @var bool
     */
    private $withParents = false;

    /**
     * @var DirService
     */
    private $dirService;

    /**
     * @var GibsonStoreService
     */
    private $gibsonStoreService;

    public function __construct(DirService $dirService, GibsonStoreService $gibsonStoreService)
    {
        $this->dirService = $dirService;
        $this->gibsonStoreService = $gibsonStoreService;
    }

    public function getList(): array
    {
        if ($this->withParents) {
            return $this->loadParentDir($this->dir, array_values($this->loadDir($this->dir)));
        }

        return array_values($this->loadDir($this->dir));
    }

    public function getCount(): int
    {
        return 0;
    }

    public function setHomePath(string $homePath): DirListStore
    {
        $this->homePath = $this->dirService->addEndSlash($homePath);

        return $this;
    }

    public function setDir(string $dir): DirListStore
    {
        $this->dir = $this->dirService->addEndSlash($dir);

        return $this;
    }

    public function setWithParents(bool $withParents): DirListStore
    {
        $this->withParents = $withParents;

        return $this;
    }

    /**
     * @throws GetError
     * @throws ExecuteError
     * @throws ReadError
     */
    private function loadDir(string $dir): array
    {
        $dirs = [];

        foreach ($this->dirService->getFiles($dir) as $file) {
            if (!is_dir($file)) {
                continue;
            }

            $item = $this->getItem($file);
            $dirs[$item['id']] = $item;
        }

        return $dirs;
    }

    private function loadParentDir(string $dir, array $data): array
    {
        $dir = $this->dirService->removeEndSlash($dir);
        $dirWithoutHomePath = preg_replace(
            '/^' . preg_quote($this->homePath, '/') . '/',
            '',
            $dir,
            1,
            $hits
        );

        if ($hits === 0 || empty($dirWithoutHomePath)) {
            $item = $this->getItem($dir);

            if (!empty($data)) {
                $item['expanded'] = true;
                $item['data'] = $data;
            }

            return [$item];
        }

        $dirParts = explode(DIRECTORY_SEPARATOR, $dir);
        array_pop($dirParts);

        $parentDir = implode(DIRECTORY_SEPARATOR, $dirParts);
        $dirs = $this->loadDir($parentDir);

        if (!empty($data)) {
            $dirs[$dir . DIRECTORY_SEPARATOR]['expanded'] = true;
            $dirs[$dir . DIRECTORY_SEPARATOR]['data'] = $data;
        }

        return $this->loadParentDir($parentDir, array_values($dirs));
    }

    private function getItem(string $file): array
    {
        $iconCls = 'icon_dir';

        try {
            $icon = $this->gibsonStoreService->getDirMeta($file, 'icon');

            if (!empty($icon)) {
                $iconCls = $icon;
            }
        } catch (ExecuteError $e) {
            // Write error
        }

        $dirParts = explode(DIRECTORY_SEPARATOR, $file);
        $id = $this->dirService->addEndSlash($file);

        return [
            'id' => $id,
            'text' => array_pop($dirParts),
            'iconCls' => 'icon16 ' . $iconCls,
        ];
    }
}
