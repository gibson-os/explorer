<?php
namespace GibsonOS\Module\Explorer\Store\Html5;

use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Service\ModuleSetting;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Utility\Dir;
use GibsonOS\Core\Utility\File\Type;

class Media extends AbstractDatabaseStore
{
    /**
     * @return string
     */
    protected function getTableName()
    {
        return 'explorer_html5_media';
    }

    /**
     * @return string
     */
    protected function getCountField()
    {
        return '`id`';
    }

    /**
     * @return array
     * @throws SelectError
     */
    public function getList()
    {
        $mediaPath = ModuleSetting::getInstance()->getByRegistry('html5_media_path')->getValue();

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
                'id' => (int)$media->id,
                'html5VideoToken' => $media->token,
                'category' => Type::TYPE_CATEGORY_VIDEO,
                'dir' => $media->dir,
                'filename' => $media->filename,
                'status' => $media->status,
                'added' => $media->added,
                'size' => $size,
                'type' => Type::getFileType($path),
                'thumbAvailable' => Type::getThumbType($path) ? true : false
            ];
        }

        return $records;
    }

    /**
     * @return int
     * @throws SelectError
     */
    public function getSize()
    {
        $mediaPath = ModuleSetting::getInstance()->getByRegistry('html5_media_path')->getValue();
        $size = 0;

        foreach (glob(Dir::escapeForGlob($mediaPath) . '*') as $file) {
            $size += filesize($file);
        }

        return $size;
    }

    /**
     * @return string[]
     */
    protected function getOrderMapping()
    {
        return [
            'filename' => 'filename',
            'dir' => 'dir',
            'status' => 'status',
            'added' => 'added',
        ];
    }
}