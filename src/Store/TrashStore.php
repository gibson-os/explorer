<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Module\Explorer\Model\Trash;

class TrashStore extends AbstractDatabaseStore
{
    protected function getTableName(): string
    {
        return Trash::getTableName();
    }

    protected function getCountField(): string
    {
        return '`token`';
    }

    protected function getOrderMapping(): array
    {
        return [];
    }

    /**
     * @throws DateTimeError
     */
    public function getList(): iterable
    {
        if (!$this->table->select()) {
            return [];
        };

        do {
            $model = new Trash();
            $model->loadFromMysqlTable($this->table);

            yield $model;
        } while ($this->table->next());
    }
}