<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store\Html5;

use Generator;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\File\TypeService;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use mysqlDatabase;

class MediaStore extends AbstractDatabaseStore
{
    public function __construct(
        mysqlDatabase $database,
        private TypeService $typeService,
        private DirService $dir,
        #[GetSetting('html5_media_path')] private Setting $html5MediaPath
    ) {
        parent::__construct($database);
    }

    protected function getModelClassName(): string
    {
        return Media::class;
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    public function getList(): Generator
    {
        $mediaPath = $this->html5MediaPath->getValue();

        /** @var Media $media */
        foreach (parent::getList() as $media) {
            $path = $media->getDir() . $media->getFilename();
            $data = $media->jsonSerialize();

            if (
                $media->getStatus() == 'generate' ||
                $media->getStatus() == 'generated'
            ) {
                $data['size'] = filesize($mediaPath . $media->getToken() . '.mp4');
            }

            $data['category'] = TypeService::TYPE_CATEGORY_VIDEO;
            $data['type'] = $this->typeService->getFileType($path);
            $data['thumbAvailable'] = $this->typeService->getThumbType($path) ? true : false;

            yield $data;
        }
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     */
    public function getSize(): int
    {
        $size = 0;

        foreach ($this->dir->getFiles($this->html5MediaPath->getValue()) as $file) {
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
