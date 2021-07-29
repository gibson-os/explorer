<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Repository\Html5;

use DateTime;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Explorer\Model\Html5\Media as MediaModel;

/**
 * @psalm-suppress MoreSpecificReturnType
 * @psalm-suppress LessSpecificReturnStatement
 */
class MediaRepository extends AbstractRepository
{
    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getByToken(string $token): MediaModel
    {
        $model = $this->fetchOne('`token`=?', [$token], MediaModel::class);

        if (!$model instanceof MediaModel) {
            throw new SelectError();
        }

        return $model;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     *
     * @return MediaModel[]
     */
    public function getByTokens(array $tokens): array
    {
        return $this->fetchAll(
            '`token` IN (' . implode(', ', array_fill(0, count($tokens), '?')) . ')',
            $tokens,
            MediaModel::class
        );
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getByDirAndFilename(string $dir, string $filename): MediaModel
    {
        $model = $this->fetchOne('`dir`=? AND `filename`=?', [$dir, $filename], MediaModel::class);

        if (!$model instanceof MediaModel) {
            throw new SelectError();
        }

        return $model;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     *
     * @return MediaModel[]
     */
    public function getAllByStatus(string $status): array
    {
        return $this->fetchAll('`status`=?', [$status], MediaModel::class);
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     *
     * @return MediaModel[]
     */
    public function getAllOlderThan(DateTime $date): array
    {
        return $this->fetchAll(
            '`added`<?',
            [$date->format('Y-m-d H:i:s')],
            MediaModel::class
        );
    }

    /**
     * @throws DateTimeError
     */
    public function getFreeToken(): string
    {
        $token = md5((string) rand());

        try {
            $this->fetchOne('`token`=?', [$token], MediaModel::class);

            return $this->getFreeToken();
        } catch (SelectError $e) {
            return $token;
        }
    }
}
