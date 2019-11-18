<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Repository\Html5;

use DateTime;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\AbstractRepository;
use GibsonOS\Module\Explorer\Model\Html5\Media as MediaModel;

class MediaRepository extends AbstractRepository
{
    /**
     * @param string $token
     *
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     *
     * @return MediaModel
     */
    public static function getByToken($token)
    {
        $table = self::getTable(MediaModel::getTableName());
        $table->setWhere('`token`=' . self::escape($token));
        $table->setLimit(1);

        if (!$table->select()) {
            $exception = new SelectError('Kein Media mit dem Token ' . $token . ' gefunden!');
            $exception->setTable($table);

            throw $exception;
        }

        $model = new MediaModel();
        $model->loadFromMysqlTable($table);

        return $model;
    }

    /**
     * @param string $status
     *
     * @throws SelectError
     * @throws DateTimeError
     * @throws GetError
     *
     * @return MediaModel[]
     */
    public static function getAllByStatus($status)
    {
        $table = self::getTable(MediaModel::getTableName());
        $table->setWhere('`status`=' . self::escape($status));

        if ($table->select() === false) {
            $exception = new SelectError();
            $exception->setTable($table);

            throw $exception;
        }

        $models = [];

        if ($table->countRecords() === 0) {
            return $models;
        }

        do {
            $model = new MediaModel();
            $model->loadFromMysqlTable($table);
            $models[] = $model;
        } while ($table->next());

        return $models;
    }

    /**
     * @param DateTime $date
     *
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     *
     * @return array
     */
    public static function getAllOlderThan(DateTime $date)
    {
        $table = self::getTable(MediaModel::getTableName());
        $table->setWhere('`added`<' . self::escape($date->format('Y-m-d H:i:s')));

        if ($table->select() === false) {
            $exception = new SelectError();
            $exception->setTable($table);

            throw $exception;
        }

        $models = [];

        if ($table->countRecords() === 0) {
            return $models;
        }

        do {
            $model = new MediaModel();
            $model->loadFromMysqlTable($table);
            $models[] = $model;
        } while ($table->next());

        return $models;
    }
}
