<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Dto;

use JsonSerializable;

class Dir implements JsonSerializable
{
    private string $path;

    private string $name;

    private int $size = 0;

    private int $files = 0;

    private int $dirs = 0;

    private int $dirFiles = 0;

    private int $dirDirs = 0;

    private ?string $icon = null;

    private ?int $accessed = null;

    private ?int $modified = null;

    public function __construct(string $path, string $name)
    {
        $this->path = $path;
        $this->name = $name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): Dir
    {
        $this->path = $path;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Dir
    {
        $this->name = $name;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): Dir
    {
        $this->size = $size;

        return $this;
    }

    public function getFiles(): int
    {
        return $this->files;
    }

    public function setFiles(int $files): Dir
    {
        $this->files = $files;

        return $this;
    }

    public function getDirs(): int
    {
        return $this->dirs;
    }

    public function setDirs(int $dirs): Dir
    {
        $this->dirs = $dirs;

        return $this;
    }

    public function getDirFiles(): int
    {
        return $this->dirFiles;
    }

    public function setDirFiles(int $dirFiles): Dir
    {
        $this->dirFiles = $dirFiles;

        return $this;
    }

    public function getDirDirs(): int
    {
        return $this->dirDirs;
    }

    public function setDirDirs(int $dirDirs): Dir
    {
        $this->dirDirs = $dirDirs;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): Dir
    {
        $this->icon = $icon;

        return $this;
    }

    public function getAccessed(): ?int
    {
        return $this->accessed;
    }

    public function setAccessed(?int $accessed): Dir
    {
        $this->accessed = $accessed;

        return $this;
    }

    public function getModified(): ?int
    {
        return $this->modified;
    }

    public function setModified(?int $modified): Dir
    {
        $this->modified = $modified;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'path' => $this->getPath() ?: '/',
            'name' => $this->getName(),
            'type' => 'dir',
            'size' => $this->getSize(),
            'files' => $this->getFiles(),
            'dirs' => $this->getDirs(),
            'dirFiles' => $this->getDirFiles(),
            'dirDirs' => $this->getDirDirs(),
            'icon' => $this->getIcon(),
            'accessed' => $this->getAccessed(),
            'modified' => $this->getModified(),
        ];
    }
}
