<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Model;

use DateTimeInterface;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use JsonSerializable;

class Trash extends AbstractModel implements JsonSerializable
{
    private string $token;

    private string $dir;

    private ?string $filename = null;

    private DateTimeInterface $added;

    private ?int $userId = null;

    private ?User $user = null;

    public static function getTableName(): string
    {
        return 'explorer_trash';
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): Trash
    {
        $this->token = $token;

        return $this;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function setDir(string $dir): Trash
    {
        $this->dir = $dir;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): Trash
    {
        $this->filename = $filename;

        return $this;
    }

    public function getAdded(): DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeInterface $added): Trash
    {
        $this->added = $added;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): Trash
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUser(): ?User
    {
        $userId = $this->getUserId();

        if ($userId === null) {
            $this->user = null;
        } else {
            $this->user = new User();
            $this->loadForeignRecord($this->user, $userId);
        }

        return $this->user;
    }

    public function setUser(?User $user): Trash
    {
        $this->user = $user;
        $this->setUserId($user?->getId());

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'token' => $this->getToken(),
            'dir' => $this->getDir(),
            'filename' => $this->getFilename(),
            'userId' => $this->getUserId(),
            'username' => $this->getUser()?->getUser(),
            'added' => $this->getAdded()->format('Y-m-d H:i:s'),
        ];
    }
}
