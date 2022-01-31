<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Module\Explorer\Model\Trash;
use mysqlDatabase;

class TrashStore extends AbstractDatabaseStore
{
    public function __construct(
        private FileService $fileService,
        mysqlDatabase $database = null
    ) {
        parent::__construct($database);
    }

    protected function getModelClassName(): string
    {
        return Trash::class;
    }

    protected function getCountField(): string
    {
        return '`token`';
    }

    protected function getDefaultOrder(): string
    {
        return '`added`';
    }

    protected function getOrderMapping(): array
    {
        return [
            'dir' => 'CONCAT(`dir`, `filename`)',
            'added' => '`added`',
        ];
    }

    /**
     * @throws SelectError
     */
    public function getList(): iterable
    {
        /** @var Trash $trash */
        foreach (parent::getList() as $trash) {
            $data = $trash->jsonSerialize();
            $data['type'] = 'dir';

            if ($trash->getFilename() !== null) {
                $data['type'] = $this->fileService->getFileEnding($trash->getFilename() ?? '');
            }

            yield $data;
        }
    }
}
