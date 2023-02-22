<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Repository\Html5\Media;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Explorer\Model\Html5\ConnectedUser;
use GibsonOS\Module\Explorer\Model\Html5\Media\Position;

class PositionRepository extends AbstractRepository
{
    public function __construct(
        #[GetTableName(Position::class)] private readonly string $positionTableName,
        #[GetTableName(ConnectedUser::class)] private readonly string $connectedUserTableName,
    ) {
    }

    /**
     * @throws SelectError
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

    /**
     * @throws SelectError
     */
    public function getByMediaAndConnectedUserId(int $mediaId, int $userId): array
    {
        $table = $this->getTable($this->positionTableName)
            ->appendJoinLeft(
                sprintf('`%s` `cu`', $this->connectedUserTableName),
                sprintf(
                    '`%s`.`user_id`=`cu`.`user_id` OR `%s`.`user_id`=`cu`.`connected_user_id`',
                    $this->positionTableName,
                    $this->positionTableName,
                ),
            )
            ->setWhere(sprintf(
                '(`cu`.`user_id`=? OR `cu`.`connected_user_id`=?) AND `%s`.`media_id`=?',
                $this->positionTableName,
            ))
            ->setWhereParameters([$userId, $userId, $mediaId])
        ;

        return $this->getModels($table, Position::class);
    }
}
