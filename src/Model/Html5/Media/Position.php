<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Model\Html5\Media;

use DateTime;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use mysqlDatabase;

class Position extends AbstractModel
{
    /**
     * @var int
     */
    private $mediaId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $position;

    /**
     * @var DateTime
     */
    private $modified;

    /**
     * @var Media
     */
    private $media;

    /**
     * @var User
     */
    private $user;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->media = new Media();
        $this->user = new User();
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'explorer_html5_media_position';
    }

    /**
     * @return int
     */
    public function getMediaId(): int
    {
        return $this->mediaId;
    }

    /**
     * @param int $mediaId
     *
     * @return Position
     */
    public function setMediaId(int $mediaId): Position
    {
        $this->mediaId = $mediaId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return Position
     */
    public function setUserId(int $userId): Position
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return Position
     */
    public function setPosition(int $position): Position
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getModified(): DateTime
    {
        return $this->modified;
    }

    /**
     * @param DateTime $modified
     *
     * @return Position
     */
    public function setModified(DateTime $modified): Position
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * @return Media
     */
    public function getMedia(): Media
    {
        return $this->media;
    }

    /**
     * @param Media $media
     *
     * @return Position
     */
    public function setMedia(Media $media): Position
    {
        $this->media = $media;
        $this->setMediaId($media->getId());

        return $this;
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     *
     * @return Position
     */
    public function loadMedia(): Position
    {
        $this->loadForeignRecord($this->getMedia(), $this->getMediaId());

        return $this;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     *
     * @return User
     */
    public function getUser(): User
    {
        $this->loadForeignRecord($this->user, $this->getUserId());

        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Position
     */
    public function setUser(User $user): Position
    {
        $this->user = $user;
        $this->setUserId($user->getId());

        return $this;
    }
}
