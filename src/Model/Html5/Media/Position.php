<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Model\Html5\Media;

use DateTimeInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use mysqlDatabase;

class Position extends AbstractModel
{
    private int $mediaId;

    private int $userId;

    private int $position;

    private DateTimeInterface $modified;

    private Media $media;

    private User $user;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->media = new Media();
        $this->user = new User();
    }

    public static function getTableName(): string
    {
        return 'explorer_html5_media_position';
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

    public function getModified(): DateTimeInterface
    {
        return $this->modified;
    }

    public function setModified(DateTimeInterface $modified): Position
    {
        $this->modified = $modified;

        return $this;
    }

    public function getMedia(): Media
    {
        return $this->media;
    }

    public function setMedia(Media $media): Position
    {
        $this->media = $media;
        $this->setMediaId($media->getId() ?? 0);

        return $this;
    }

    /**
     * @throws DateTimeError
     */
    public function loadMedia(): Position
    {
        $this->loadForeignRecord($this->getMedia(), $this->getMediaId());

        return $this;
    }

    /**
     * @throws DateTimeError
     */
    public function getUser(): User
    {
        $this->loadForeignRecord($this->user, $this->getUserId());

        return $this->user;
    }

    public function setUser(User $user): Position
    {
        $this->user = $user;
        $this->setUserId($user->getId() ?? 0);

        return $this;
    }
}
