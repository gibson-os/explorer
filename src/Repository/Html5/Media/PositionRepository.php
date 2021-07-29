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
        $model = $this->fetchOne(
            '`media_id`=? AND `user_id`=?',
            [$mediaId, $userId],
            Position::class
        );

        if (!$model instanceof Position) {
            throw new SelectError();
        }

        return $model;
    }
}
