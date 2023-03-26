<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store\Html5;

use DateTime;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Module\Explorer\Model\Html5\ConnectedUser;
use JsonException;
use mysqlDatabase;
use ReflectionException;

class ConnectedUserStore extends AbstractDatabaseStore
{
    private User $user;

    public function __construct(
        #[GetTableName(User::class)] private readonly string $userTableName,
        mysqlDatabase $database = null,
    ) {
        parent::__construct($database);
    }

    protected function getModelClassName(): string
    {
        return ConnectedUser::class;
    }

    public function setUser(User $user): ConnectedUserStore
    {
        $this->user = $user;

        return $this;
    }

    protected function setWheres(): void
    {
        $this->addWhere(
            sprintf('`%s`.`user_id`=? OR `%s`.`connected_user_id`=?', $this->tableName, $this->tableName),
            [$this->user->getId(), $this->user->getId()],
        );
    }

    protected function initTable(): void
    {
        parent::initTable();

        $this->table
            ->appendJoin($this->userTableName . ' `cu`', sprintf(
                '`cu`.`id`=`%s`.`connected_user_id`',
                $this->tableName,
            ))
            ->appendJoin($this->userTableName . ' `u`', sprintf(
                '`u`.`id`=`%s`.`user_id`',
                $this->tableName,
            ))
            ->setSelectString(sprintf(
                '`%s`.`id`, ' .
                '`%s`.`user_id`, ' .
                '`%s`.`connected_user_id`, ' .
                '`cu`.`user` `connected_user_name`, ' .
                '`cu`.`added` `connected_user_added`, ' .
                '`u`.`user` `user_name`, ' .
                '`u`.`added` `user_added`',
                $this->tableName,
                $this->tableName,
                $this->tableName,
            ));
    }

    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function getModel(): ConnectedUser
    {
        /** @var ConnectedUser $model */
        $model = parent::getModel();
        $record = $this->table->getSelectedRecord();

        $connectedUser = (new User())
            ->setId((int) $record['connected_user_id'])
            ->setUser($record['connected_user_name'])
            ->setAdded(new DateTime($record['connected_user_added']))
        ;

        if ($this->user->getId() === $connectedUser->getId()) {
            $connectedUser
                ->setId((int) $record['user_id'])
                ->setUser($record['user_name'])
            ;
        }

        $model
            ->setUser($this->user)
            ->setConnectedUser($connectedUser)
        ;

        return $model;
    }
}
