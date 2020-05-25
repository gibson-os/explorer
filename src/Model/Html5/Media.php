<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Model\Html5;

use DateTimeInterface;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use mysqlDatabase;

class Media extends AbstractModel
{
    public const SUBTITLE_NONE = 'none';

    public const STATUS_ERROR = 'error';

    public const STATUS_WAIT = 'wait';

    public const STATUS_GENERATE = 'generate';

    public const STATUS_GENERATED = 'generated';

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
     * @var string|null
     */
    private $subtitleStream;

    /**
     * @var string
     */
    private $status = self::STATUS_WAIT;

    /**
     * @var int
     */
    private $type;

    /**
     * @var DateTimeInterface
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

    public static function getTableName(): string
    {
        return 'explorer_html5_media';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): Media
    {
        $this->id = $id;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): Media
    {
        $this->token = $token;

        return $this;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function setDir(string $dir): Media
    {
        $this->dir = $dir;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): Media
    {
        $this->filename = $filename;

        return $this;
    }

    public function getAudioStream(): ?string
    {
        return $this->audioStream;
    }

    public function setAudioStream(?string $audioStream): Media
    {
        $this->audioStream = $audioStream;

        return $this;
    }

    public function getSubtitleStream(): ?string
    {
        return $this->subtitleStream;
    }

    public function setSubtitleStream(?string $subtitleStream): Media
    {
        $this->subtitleStream = $subtitleStream;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): Media
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): Media
    {
        $this->type = $type;

        return $this;
    }

    public function getAdded(): DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(DateTimeInterface $added): Media
    {
        $this->added = $added;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): Media
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getUser(): User
    {
        $this->loadForeignRecord($this->user, $this->getUserId());

        return $this->user;
    }

    public function setUser(User $user): Media
    {
        $this->user = $user;
        $this->setUserId($user->getId() ?? 0);

        return $this;
    }
}
