<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Repository;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Explorer\Model\Trash;

class TrashRepository extends AbstractRepository
{
    /**
     * @throws DateTimeError
     */
    public function getFreeToken(): string
    {
        try {
            $token = md5((string) rand());
            $this->fetchOne('`token`=?', [$token], Trash::class);

            return $token;
        } catch (SelectError $e) {
            return $this->getFreeToken();
        }
    }
}
