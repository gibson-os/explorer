<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Dto;

use JsonSerializable;

class File implements JsonSerializable
{
    private string $name;

    private int $size;

    private string $type;

    private int $category;

    private bool $thumbAvailable = false;

    private ?string $html5MediaStatus = null;

    private ?string $html5MediaToken = null;

    private ?int $position = null;

    private ?int $accessed = null;

    private ?int $modified = null;

    private ?array $metaInfos = null;

    public function __construct(string $name, string $type, int $size, int $category)
    {
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->category = $category;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): File
    {
        $this->name = $name;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): File
    {
        $this->size = $size;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): File
    {
        $this->type = $type;

        return $this;
    }

    public function getCategory(): int
    {
        return $this->category;
    }

    public function setCategory(int $category): File
    {
        $this->category = $category;

        return $this;
    }

    public function isThumbAvailable(): bool
    {
        return $this->thumbAvailable;
    }

    public function setThumbAvailable(bool $thumbAvailable): File
    {
        $this->thumbAvailable = $thumbAvailable;

        return $this;
    }

    public function getHtml5MediaStatus(): ?string
    {
        return $this->html5MediaStatus;
    }

    public function setHtml5MediaStatus(?string $html5MediaStatus): File
    {
        $this->html5MediaStatus = $html5MediaStatus;

        return $this;
    }

    public function getHtml5MediaToken(): ?string
    {
        return $this->html5MediaToken;
    }

    public function setHtml5MediaToken(?string $html5MediaToken): File
    {
        $this->html5MediaToken = $html5MediaToken;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): File
    {
        $this->position = $position;

        return $this;
    }

    public function getAccessed(): ?int
    {
        return $this->accessed;
    }

    public function setAccessed(?int $accessed): File
    {
        $this->accessed = $accessed;

        return $this;
    }

    public function getModified(): ?int
    {
        return $this->modified;
    }

    public function setModified(?int $modified): File
    {
        $this->modified = $modified;

        return $this;
    }

    public function getMetaInfos(): ?array
    {
        return $this->metaInfos;
    }

    public function setMetaInfos(?array $metaInfos): File
    {
        $this->metaInfos = $metaInfos;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->getName(),
            'size' => $this->getSize(),
            'type' => $this->getType(),
            'thumbAvailable' => $this->isThumbAvailable(),
            'html5VideoStatus' => $this->getHtml5MediaStatus(),
            'html5MediaStatus' => $this->getHtml5MediaStatus(),
            'html5VideoToken' => $this->getHtml5MediaToken(),
            'html5MediaToken' => $this->getHtml5MediaToken(),
            'position' => $this->getPosition(),
            'category' => $this->getCategory(),
            'accessed' => $this->getAccessed(),
            'modified' => $this->getModified(),
            'metaInfos' => $this->getMetaInfos() ?: null,
        ];
    }
}
