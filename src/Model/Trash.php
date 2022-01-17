<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Model;

use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use JsonSerializable;

/**
 * @method User|null getUser()
 * @method Trash     setUser(?User $user)
 */
#[Table]
#[Key(columns: ['dir', 'filename'])]
class Trash extends AbstractModel implements JsonSerializable
{
    #[Column(length: 32, primary: true)]
    private string $token;

    #[Column(length: 255)]
    private string $dir;

    #[Column(length: 128)]
    private ?string $filename = null;

    #[Column(type: Column::TYPE_TIMESTAMP, default: Column::DEFAULT_CURRENT_TIMESTAMP)]
    private DateTimeInterface $added;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private ?int $userId = null;

    #[Constraint]
    protected ?User $user = null;

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
