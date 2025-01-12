<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Service\DirService as CoreDirService;
use GibsonOS\Core\Store\AbstractStore;
use GibsonOS\Module\Explorer\Service\DirService;
use GibsonOS\Module\Explorer\Service\FileService;

class DirStore extends AbstractStore
{
    private string $dir;

    private array $list = [];

    private array $metas = [];

    private ?int $userId = null;

    public function __construct(private CoreDirService $coreDirService, private DirService $dirService, private FileService $fileService)
    {
        $this->reset();
    }

    /**
     * @throws DateTimeError
     * @throws FactoryError
     * @throws GetError
     * @throws ReadError
     */
    public function getList(): array
    {
        $this->generateList();

        return $this->list;
    }

    /**
     * @throws DateTimeError
     * @throws FactoryError
     * @throws GetError
     * @throws ReadError
     */
    public function getCount(): int
    {
        return count($this->getList());
    }

    /**
     * @throws DateTimeError
     * @throws FactoryError
     * @throws GetError
     * @throws ReadError
     */
    public function getMetas(): array
    {
        $this->generateList();

        return $this->metas;
    }

    public function setDir(string $dir): DirStore
    {
        $this->dir = $dir;

        return $this;
    }

    public function setUserId(?int $user): void
    {
        $this->userId = $user;
    }

    public function reset(): void
    {
        $this->list = [];
        $this->metas = [
            'dircount' => 0,
            'dirdircount' => 0,
            'dirfilecount' => 0,
            'dirsize' => 0,
            'filecount' => 0,
            'filesize' => 0,
        ];
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws ReadError
     * @throws DateTimeError
     */
    private function generateList(): void
    {
        if ($this->list !== []) {
            return;
        }

        $dirs = [];
        $dirsLower = [];
        $files = [];
        $filesLower = [];

        foreach ($this->coreDirService->getFiles($this->dir) as $filename) {
            $filenameLower = mb_strtolower($filename) ?: $filename;

            if (is_dir($filename)) {
                $dirsLower[] = $filenameLower;
                $dirs[$filenameLower] = $filename;
            } else {
                $filesLower[] = $filenameLower;
                $files[$filenameLower] = $filename;
            }
        }

        sort($dirsLower);
        sort($filesLower);

        foreach ($dirsLower as $path) {
            $path = $dirs[$path];
            $item = $this->dirService->get($path);

            $this->metas['dirsize'] += $item->getSize();
            $this->metas['dirfilecount'] += $item->getFiles() + $item->getDirFiles();
            $this->metas['dirdircount'] += $item->getDirs() + $item->getDirDirs();
            ++$this->metas['dircount'];

            $this->list[] = $item;
        }

        foreach ($filesLower as $path) {
            $path = $files[$path];

            $item = $this->fileService->get($path, $this->userId);
            $this->metas['filesize'] += $item->getSize();
            ++$this->metas['filecount'];

            $this->list[] = $item;
        }

        $this->metas['dirsize'] += $this->metas['filesize'];

        $dir = $this->dirService->get($this->dir)
            ->setSize($this->metas['dirsize'])
            ->setFiles($this->metas['filecount'])
            ->setDirFiles($this->metas['dirfilecount'])
            ->setDirs($this->metas['dircount'])
            ->setDirDirs($this->metas['dirdircount'])
        ;

        try {
            $this->dirService->set($dir);
        } catch (ExecuteError|WriteError) {
            // write error
        }
    }
}
