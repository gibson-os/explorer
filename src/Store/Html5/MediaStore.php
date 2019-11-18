<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store\Html5;

use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\File\TypeService;
use GibsonOS\Core\Service\ModuleSettingService;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use mysqlDatabase;

class MediaStore extends AbstractDatabaseStore
{
    /**
     * @var ModuleSettingService
     */
    private $moduleSetting;

    /**
     * @var TypeService
     */
    private $typeService;

    /**
     * @var DirService
     */
    private $dir;

    public function __construct(
        mysqlDatabase $database,
        ModuleSettingService $moduleSetting,
        TypeService $typeService,
        DirService $dir
    ) {
        parent::__construct($database);

        $this->moduleSetting = $moduleSetting;
        $this->typeService = $typeService;
        $this->dir = $dir;
    }

    protected function getTableName(): string
    {
        return 'explorer_html5_media';
    }

    protected function getCountField(): string
    {
        return '`id`';
    }

    /**
     * @throws SelectError
     * @throws DateTimeError
     * @throws GetError
     *
     * @return array
     */
    public function getList(): array
    {
        /** @var Setting $html5MediaPath */
        $html5MediaPath = $this->moduleSetting->getByRegistry('html5_media_path');
        $mediaPath = $html5MediaPath->getValue();

        $this->table->setOrderBy($this->getOrderBy());
        $this->table->select(false);
        $records = [];

        foreach ($this->table->connection->fetchObjectList() as $media) {
            $path = $media->dir . $media->filename;
            $size = 0;

            if (
                $media->status == 'generate' ||
                $media->status == 'generated'
            ) {
                $size = filesize($mediaPath . $media->token . '.mp4');
            }

            $records[] = [
                'id' => (int) $media->id,
                'html5VideoToken' => $media->token,
                'category' => TypeService::TYPE_CATEGORY_VIDEO,
                'dir' => $media->dir,
                'filename' => $media->filename,
                'status' => $media->status,
                'added' => $media->added,
                'size' => $size,
                'type' => $this->typeService->getFileType($path),
                'thumbAvailable' => $this->typeService->getThumbType($path) ? true : false,
            ];
        }

        return $records;
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     *
     * @return int
     */
    public function getSize()
    {
        /** @var Setting $html5MediaPath */
        $html5MediaPath = $this->moduleSetting->getByRegistry('html5_media_path');
        $mediaPath = $html5MediaPath->getValue();
        $size = 0;

        foreach ($this->dir->getFiles($mediaPath) as $file) {
            $size += filesize($file);
        }

        return $size;
    }

    /**
     * @return string[]
     */
    protected function getOrderMapping(): array
    {
        return [
            'filename' => 'filename',
            'dir' => 'dir',
            'status' => 'status',
            'added' => 'added',
        ];
    }
}
