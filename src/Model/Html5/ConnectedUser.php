<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Model\Html5;

use GibsonOS\Core\Attribute\Install\Database\Column;
use GibsonOS\Core\Attribute\Install\Database\Constraint;
use GibsonOS\Core\Attribute\Install\Database\Key;
use GibsonOS\Core\Attribute\Install\Database\Table;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Model\User;

/**
 * @method ConnectedUser setUser(User $user)
 * @method User          getUser()
 * @method ConnectedUser setConnectedUser(User $connectedUser)
 * @method User          getConnectedUser()
 */
#[Table]
#[Key(true, ['user_id', 'connected_user_id'])]
class ConnectedUser extends AbstractModel implements \JsonSerializable
{
    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED], autoIncrement: true)]
    private ?int $id = null;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $userId;

    #[Column(attributes: [Column::ATTRIBUTE_UNSIGNED])]
    private int $connectedUserId;

    #[Constraint]
    protected User $user;

    #[Constraint(name: 'fkExplorer_html5_connected_userConnectedUser', ownColumn: 'connected_user_id')]
    protected User $connectedUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getConnectedUserId(): int
    {
        return $this->connectedUserId;
    }

    public function setConnectedUserId(int $connectedUserId): void
    {
        $this->connectedUserId = $connectedUserId;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'user' => $this->getUser(),
            'connectedUser' => $this->getConnectedUser(),
        ];
    }
}
