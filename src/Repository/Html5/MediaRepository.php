<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Repository\Html5;

use DateTime;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Explorer\Model\Html5\Media;

class MediaRepository extends AbstractRepository
{
    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getByToken(string $token): Media
    {
        $model = $this->fetchOne('`token`=?', [$token], Media::class);

        if (!$model instanceof Media) {
            throw new SelectError();
        }

        return $model;
    }

    /**
     *@throws SelectError
     * @throws DateTimeError
     *
     * @return Media[]
     */
    public function getByTokens(array $tokens): array
    {
        return $this->fetchAll(
            '`token` IN (' . implode(', ', array_fill(0, count($tokens), '?')) . ')',
            $tokens,
            Media::class
        );
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getByDirAndFilename(string $dir, string $filename): Media
    {
        $model = $this->fetchOne('`dir`=? AND `filename`=?', [$dir, $filename], Media::class);

        if (!$model instanceof Media) {
            throw new SelectError();
        }

        return $model;
    }

    /**
     *@throws SelectError
     * @throws DateTimeError
     *
     * @return Media[]
     */
    public function getAllByStatus(string $status): array
    {
        return $this->fetchAll('`status`=?', [$status], Media::class);
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     *
     * @return Media[]
     */
    public function getAllGeneratedOrderByViewed(): array
    {
        $mediaTableName = Media::getTableName();
        $mediaPositionTableName = Media::getTableName();
        $table = $this->getTable($mediaTableName)
            ->setWhere('`status`=?')
            ->addWhereParameter(Media::STATUS_GENERATED)
            ->appendJoin(
                $mediaPositionTableName,
                '`' . $mediaTableName . '`.`id`=`' . $mediaPositionTableName . '`.`media_id`'
            )
            ->setOrderBy()
        ;

        return $this->getModels($table, Media::class);
    }

    /**
     *@throws SelectError
     * @throws DateTimeError
     *
     * @return Media[]
     */
    public function getAllOlderThan(DateTime $date): array
    {
        return $this->fetchAll(
            '`added`<?',
            [$date->format('Y-m-d H:i:s')],
            Media::class
        );
    }

    /**
     * @throws DateTimeError
     */
    public function getFreeToken(): string
    {
        $token = md5((string) rand());

        try {
            $this->fetchOne('`token`=?', [$token], Media::class);

            return $this->getFreeToken();
        } catch (SelectError) {
            return $token;
        }
    }
}
