<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store\Html5;

use GibsonOS\Core\Dto\Model\ChildrenMapping;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use GibsonOS\Module\Explorer\Model\Html5\ConnectedUser;

class ConnectedUserStore extends AbstractDatabaseStore
{
    private User $user;

    public function __construct(DatabaseStoreWrapper $databaseStoreWrapper)
    {
        parent::__construct($databaseStoreWrapper);
    }

    protected function getModelClassName(): string
    {
        return ConnectedUser::class;
    }

    protected function getAlias(): ?string
    {
        return 'cu';
    }

    public function setUser(User $user): ConnectedUserStore
    {
        $this->user = $user;

        return $this;
    }

    protected function setWheres(): void
    {
        $this->addWhere(
            '`cu`.`user_id`=:userId OR `cu`.`connected_user_id`=:userId',
            ['userId' => $this->user->getId()],
        );
    }

    protected function getExtends(): array
    {
        return [
            new ChildrenMapping('user', 'user_', 'uu'),
            new ChildrenMapping('connectedUser', 'connecteUser_', 'cuu'),
        ];
    }
}
