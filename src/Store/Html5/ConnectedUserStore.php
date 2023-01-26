<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store\Html5;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Module\Explorer\Model\Html5\ConnectedUser;

class ConnectedUserStore extends AbstractDatabaseStore
{
    private User $user;

    public function __construct(
        \mysqlDatabase $database = null,
        #[GetTableName(User::class)] private string $userTableName,
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
        $this->addWhere(sprintf('`%s`.`user_id`=?', $this->tableName), [$this->user->getId()]);
    }

    protected function initTable(): void
    {
        parent::initTable();

        $this->table
            ->appendJoin($this->userTableName, sprintf(
                '`%s`.`id`=`%s`.`connected_user_id`',
                $this->userTableName,
                $this->tableName,
            ))
            ->setSelectString(sprintf(
                '`%s`.`id`, ' .
                '`%s`.`user_id`, ' .
                '`%s`.`connected_user_id`, ' .
                '`%s`.`user` `connected_user_name`, ' .
                '`%s`.`added` `connected_user_added`',
                $this->tableName,
                $this->tableName,
                $this->tableName,
                $this->userTableName,
                $this->userTableName,
            ));
    }

    /**
     * @throws SelectError
     * @throws \JsonException
     * @throws \ReflectionException
     */
    protected function getModel(): ConnectedUser
    {
        /** @var ConnectedUser $model */
        $model = parent::getModel();
        $record = $this->table->getSelectedRecord();

        $model
            ->setUser($this->user)
            ->setConnectedUser(
                (new User())
                    ->setId((int) $record['connected_user_id'])
                    ->setUser($record['connected_user_name'])
                    ->setAdded(new \DateTime($record['connected_user_added']))
            );

        return $model;
    }
}
