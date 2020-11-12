<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Repository;

use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Explorer\Model\Trash;

class TrashRepository extends AbstractRepository
{
    public function getFreeToken(): string
    {
        $table = $this->getTable(Trash::getTableName());

        do {
            $token = md5((string) rand());
            $table->setWhere('`token`=' . $this->escape($token));
        } while ($table->select());

        return $token;
    }
}
