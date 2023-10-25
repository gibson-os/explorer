<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use GibsonOS\Module\Explorer\Model\Trash;
use JsonException;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class TrashStore extends AbstractDatabaseStore
{
    public function __construct(
        private readonly FileService $fileService,
        DatabaseStoreWrapper $databaseStoreWrapper,
    ) {
        parent::__construct($databaseStoreWrapper);
    }

    protected function getModelClassName(): string
    {
        return Trash::class;
    }

    protected function getCountField(): string
    {
        return '`token`';
    }

    protected function getDefaultOrder(): array
    {
        return ['`added`' => OrderDirection::ASC];
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
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     */
    public function getList(): iterable
    {
        /** @var Trash $trash */
        foreach (parent::getList() as $trash) {
            $data = $trash->jsonSerialize();
            $data['type'] = 'dir';
            $filename = $trash->getFilename();

            if ($filename !== null) {
                $data['type'] = $this->fileService->getFileEnding($filename);
            }

            yield $data;
        }
    }
}
