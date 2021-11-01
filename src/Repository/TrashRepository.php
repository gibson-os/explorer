<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Repository;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\AbstractModel;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Explorer\Model\Trash;

/**
 * @method Trash   fetchOne(string $where, array $parameters, string $abstractModelClassName = AbstractModel::class)
 * @method Trash[] fetchAll(string $where, array $parameters, string $abstractModelClassName = AbstractModel::class, int $limit = null, int $offset = null, string $orderBy = null)
 */
class TrashRepository extends AbstractRepository
{
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
     *
     * @throws SelectError
     *
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
     * @throws SelectError
     *
     * @return Trash[]
     */
    public function getOlderThanDays(int $days): array
    {
        return $this->fetchAll('`added`<(NOW() - INTERVAL ? DAY)', [$days], Trash::class);
    }
}
