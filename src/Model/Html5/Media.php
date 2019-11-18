<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Model\Html5;

use DateTime;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use mysqlDatabase;

class Media extends AbstractModel
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string|null
     */
    private $audioStream;

    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $type;

    /**
     * @var DateTime
     */
    private $added;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var User
     */
    private $user;

    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->user = new User();
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'explorer_html5_media';
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Media
     */
    public function setId(int $id): Media
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return Media
     */
    public function setToken(string $token): Media
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @param string $dir
     *
     * @return Media
     */
    public function setDir(string $dir): Media
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     *
     * @return Media
     */
    public function setFilename(string $filename): Media
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAudioStream(): ?string
    {
        return $this->audioStream;
    }

    /**
     * @param string|null $audioStream
     *
     * @return Media
     */
    public function setAudioStream(?string $audioStream): Media
    {
        $this->audioStream = $audioStream;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Media
     */
    public function setStatus(string $status): Media
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return Media
     */
    public function setType(int $type): Media
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getAdded(): DateTime
    {
        return $this->added;
    }

    /**
     * @param DateTime $added
     *
     * @return Media
     */
    public function setAdded(DateTime $added): Media
    {
        $this->added = $added;

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
     * @return Media
     */
    public function setUserId(int $userId): Media
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Media
     */
    public function setUser(User $user): Media
    {
        $this->user = $user;
        $this->setUserId($user->getId());

        return $this;
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     *
     * @return Media
     */
    public function loadUser(): Media
    {
        $this->loadForeignRecord($this->getUser(), $this->getUserId());

        return $this;
    }
}
