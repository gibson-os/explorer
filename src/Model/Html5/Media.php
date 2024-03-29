<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Model\Html5;

use DateTime;
use DateTimeInterface;
use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Enum\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Module\Explorer\Service\File\Type\Describer\FileTypeDescriberInterface;
use JsonSerializable;

/**
 * @method User  getUser()
 * @method Media setUser(User $user)
 */
#[Table]
#[Key(columns: ['dir', 'filename'])]
class Media extends AbstractModel implements JsonSerializable
{
    public const SUBTITLE_NONE = 'none';

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(length: 32)]
    #[Key(true)]
    private string $token;

    #[Column(length: 255)]
    private string $dir;

    #[Column(length: 255)]
    private string $filename;

    #[Column(length: 9)]
    private ?string $audioStream = null;

    #[Column(length: 9)]
    private ?string $subtitleStream = null;

    #[Column(type: Column::TYPE_ENUM, values: ['error', 'wait', 'generate', 'generated'])]
    #[Key]
    private ConvertStatus $status = ConvertStatus::WAIT;

    #[Column(type: Column::TYPE_TINYINT, attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $type = FileTypeDescriberInterface::CATEGORY_VIDEO;

    #[Column]
    private bool $locked = false;

    #[Column]
    private bool $generationRequired = true;

    #[Column(length: 1024)]
    private string $message = '';

    #[Column(type: Column::TYPE_TIMESTAMP, default: Column::DEFAULT_CURRENT_TIMESTAMP)]
    private DateTimeInterface $added;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $userId;

    #[Constraint]
    protected User $user;

    public function __construct(ModelWrapper $modelWrapper)
    {
        parent::__construct($modelWrapper);

        $this->added = new DateTime();
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

    public function getStatus(): ConvertStatus
    {
        return $this->status;
    }

    public function setStatus(ConvertStatus $status): Media
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

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): Media
    {
        $this->locked = $locked;

        return $this;
    }

    public function isGenerationRequired(): bool
    {
        return $this->generationRequired;
    }

    public function setGenerationRequired(bool $generationRequired): Media
    {
        $this->generationRequired = $generationRequired;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): Media
    {
        $this->message = $message;

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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'html5MediaToken' => $this->getToken(),
            'html5VideoToken' => $this->getToken(),
            'dir' => $this->getDir(),
            'filename' => $this->getFilename(),
            'status' => $this->getStatus()->value,
            'added' => $this->getAdded()->format('Y-m-d H:i:s'),
        ];
    }
}
