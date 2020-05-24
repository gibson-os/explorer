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
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     */
    public function getByToken(string $token): MediaModel
    {
        $table = $this->getTable(MediaModel::getTableName());
        $table->setWhere('`token`=' . $this->escape($token));
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
     * @throws SelectError
     * @throws DateTimeError
     * @throws GetError
     *
     * @return MediaModel[]
     */
    public function getAllByStatus(string $status): array
    {
        $table = $this->getTable(MediaModel::getTableName());
        $table->setWhere('`status`=' . $this->escape($status));

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
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     *
     * @return MediaModel[]
     */
    public function getAllOlderThan(DateTime $date): array
    {
        $table = $this->getTable(MediaModel::getTableName());
        $table->setWhere('`added`<' . $this->escape($date->format('Y-m-d H:i:s')));

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

    public function getFreeToken(): string
    {
        $table = $this->getTable(MediaModel::getTableName());

        do {
            $token = md5((string) rand());
            $table->setWhere('`token`=' . $this->escape($token));
        } while ($table->select());

        return $token;
    }
}
