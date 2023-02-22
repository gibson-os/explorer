<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Model\Html5\Media;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use GibsonOS\Module\Explorer\Model\Html5\Media;

/**
 * @method Media    getMedia()
 * @method Position setMedia(Media $media)
 * @method User     getUser()
 * @method Position setUser(User $user)
 */
#[Table]
class Position extends AbstractModel implements \JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $mediaId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], primary: true)]
    private int $userId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $position;

    #[Column(type: Column::TYPE_TIMESTAMP, default: Column::DEFAULT_CURRENT_TIMESTAMP, attributes: [Column::ATTRIBUTE_CURRENT_TIMESTAMP])]
    private \DateTimeInterface $modified;

    #[Constraint]
    protected Media $media;

    #[Constraint]
    protected User $user;

    public function __construct(\mysqlDatabase $database = null)
    {
        parent::__construct($database);
    }

    public function getMediaId(): int
    {
        return $this->mediaId;
    }

    public function setMediaId(int $mediaId): Position
    {
        $this->mediaId = $mediaId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): Position
    {
        $this->userId = $userId;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): Position
    {
        $this->position = $position;

        return $this;
    }

    public function getModified(): \DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(\DateTimeInterface $modified): Position
    {
        $this->modified = $modified;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'position' => $this->getPosition(),
            'user' => $this->getUser(),
        ];
    }
}
