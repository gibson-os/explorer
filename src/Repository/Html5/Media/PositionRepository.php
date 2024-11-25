<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Repository\Html5\Media;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Core\Wrapper\RepositoryWrapper;
use GibsonOS\Module\Explorer\Model\Html5\ConnectedUser;
use GibsonOS\Module\Explorer\Model\Html5\Media\Position;
use JsonException;
use MDO\Dto\Query\Join;
use MDO\Dto\Query\Where;
use MDO\Enum\JoinType;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class PositionRepository extends AbstractRepository
{
    public function __construct(
        RepositoryWrapper $repositoryWrapper,
        #[GetTableName(Position::class)]
        private readonly string $positionTableName,
        #[GetTableName(ConnectedUser::class)]
        private readonly string $connectedUserTableName,
    ) {
        parent::__construct($repositoryWrapper);
    }

    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function getByMediaAndUserId(int $mediaId, int $userId): Position
    {
        return $this->fetchOne(
            '`media_id`=? AND `user_id`=?',
            [$mediaId, $userId],
            Position::class,
        );
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     *
     * @return Position[]
     */
    public function getByMediaAndConnectedUserId(int $mediaId, int $userId): array
    {
        $selectQuery = $this->getSelectQuery($this->positionTableName, 'p')
            ->addJoin(new Join(
                $this->getTable($this->connectedUserTableName),
                'cu',
                '`p`.`user_id`=`cu`.`user_id` OR `p`.`user_id`=`cu`.`connected_user_id`',
                JoinType::LEFT,
            ))
            ->addWhere(new Where(
                '(`cu`.`user_id`=? OR `cu`.`connected_user_id`=?) AND `p`.`media_id`=?',
                [$userId, $userId, $mediaId],
            ))
        ;

        return $this->getModels($selectQuery, Position::class);
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function hasPosition(int $mediaId): bool
    {
        try {
            $this->fetchOne('`media_id`=?', [$mediaId], Position::class);
        } catch (SelectError) {
            return false;
        }

        return true;
    }
}
