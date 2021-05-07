<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Repository\Html5\Media;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Explorer\Model\Html5\Media\Position;

class PositionRepository extends AbstractRepository
{
    /**
     * @throws SelectError
     * @throws DateTimeError
     */
    public function getByMediaAndUserId(int $mediaId, int $userId): Position
    {
        $table = $this->getTable(Position::getTableName());
        $table
            ->setWhere('`media_id`=? AND `user_id=?`')
            ->setWhereParameters([$mediaId, $userId])
            ->setLimit(1)
        ;

        if (!$table->selectPrepared()) {
            throw (new SelectError())->setTable($table);
        }

        $model = new Position();
        $model->loadFromMysqlTable($table);

        return $model;
    }
}
