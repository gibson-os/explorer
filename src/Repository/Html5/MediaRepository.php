<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Repository\Html5;

use DateTime;
use GibsonOS\Core\Enum\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class MediaRepository extends AbstractRepository
{
    /**
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
     * @throws SelectError
     *
     * @return Media[]
     */
    public function getByTokens(array $tokens): array
    {
        return $this->fetchAll(
            '`token` IN (' . implode(', ', array_fill(0, count($tokens), '?')) . ')',
            $tokens,
            Media::class,
        );
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
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
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     *
     * @return Media[]
     */
    public function getAllByStatus(ConvertStatus $status): array
    {
        return $this->fetchAll('`status`=?', [$status->name], Media::class);
    }

    /**
     * @throws SelectError
     *
     * @return Media[]
     */
    //    public function getAllGeneratedOrderByViewed(): array
    //    {
    //        $mediaTableName = Media::getTableName();
    //        $mediaPositionTableName = Media::getTableName();
    //        $table = $this->getTable($mediaTableName)
    //            ->setWhere('`status`=?')
    //            ->addWhereParameter(Media::STATUS_GENERATED)
    //            ->appendJoin(
    //                $mediaPositionTableName,
    //                '`' . $mediaTableName . '`.`id`=`' . $mediaPositionTableName . '`.`media_id`'
    //            )
    //            ->setOrderBy()
    //        ;
    //
    //        return $this->getModels($table, Media::class);
    //    }

    /**
     * @throws SelectError
     *
     * @return Media[]
     */
    public function getAllOlderThan(DateTime $date): array
    {
        return $this->fetchAll(
            '`added`<?',
            [$date->format('Y-m-d H:i:s')],
            Media::class,
        );
    }

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
