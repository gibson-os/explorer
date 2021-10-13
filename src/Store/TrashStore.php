<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Module\Explorer\Model\Trash;
use mysqlDatabase;

class TrashStore extends AbstractDatabaseStore
{
    public function __construct(private FileService $fileService, mysqlDatabase $database = null)
    {
        parent::__construct($database);
    }

    protected function getTableName(): string
    {
        return Trash::getTableName();
    }

    protected function getCountField(): string
    {
        return '`token`';
    }

    protected function getOrderMapping(): array
    {
        return [
            'dir' => 'CONCAT(`dir`, `filename`)',
            'added' => '`added`',
        ];
    }

    /**
     * @throws DateTimeError
     */
    public function getList(): iterable
    {
        $this->table->setOrderBy($this->getOrderBy() ?? '`added`');

        if (!$this->table->select()) {
            return [];
        };

        do {
            $model = new Trash();
            $model->loadFromMysqlTable($this->table);
            $data = $model->jsonSerialize();
            $data['type'] = 'dir';

            if ($model->getFilename() !== null) {
                $data['type'] = $this->fileService->getFileEnding($model->getFilename() ?? '');
            }

            yield $data;
        } while ($this->table->next());
    }
}