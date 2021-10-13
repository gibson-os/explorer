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
        $token = md5((string) rand());

        try {
            $this->fetchOne('`token`=?', [$token], Trash::class);

            return $this->getFreeToken();
        } catch (SelectError) {
            return $token;
        }
    }

    /**
     * @param string[] $tokens
     * @throws DateTimeError
     * @throws SelectError
     * @return Trash[]
     */
    public function getByTokens(array $tokens): array
    {
        return $this->fetchAll(
            '`token` IN (' . implode(', ', array_fill(0, count($tokens), '?')) . ')',
            $tokens,
            Trash::class
        );
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     * @return Trash[]
     */
    public function getOlderThanDays(int $days): array
    {
        return $this->fetchAll('`added`<(NOW() - INTERVAL ? DAY)', [$days], Trash::class);
    }
}
