<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store\Html5;

use Generator;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Service\FileService;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class MediaStore extends AbstractDatabaseStore
{
    public function __construct(
        DatabaseStoreWrapper $databaseStoreWrapper,
        private readonly DirService $dirService,
        private readonly FileService $fileService,
        #[GetSetting('html5_media_path')]
        private readonly Setting $html5MediaPath,
    ) {
        parent::__construct($databaseStoreWrapper);
    }

    protected function getModelClassName(): string
    {
        return Media::class;
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SelectError
     * @throws FactoryError
     * @throws ReadError
     * @throws ClientException
     * @throws RecordException
     */
    public function getList(): Generator
    {
        $mediaPath = $this->html5MediaPath->getValue();

        /** @var Media $media */
        foreach (parent::getList() as $media) {
            $path = $media->getDir() . $media->getFilename();

            $data = $media->jsonSerialize();
            $data['size'] = 0;
            $data['category'] = 0;
            $data['type'] = '';
            $data['thumbAvailable'] = false;

            if (!file_exists($path)) {
                yield $data;

                continue;
            }

            $file = $this->fileService->get($path);

            if ($media->isGenerationRequired()) {
                $file->setSize(
                    $media->getStatus() == 'generate' || $media->getStatus() == 'generated'
                    ? filesize($mediaPath . $media->getToken() . '.mp4')
                    : 0,
                );
            }

            $data['size'] = $file->getSize();
            $data['category'] = $file->getCategory();
            $data['type'] = $file->getType();
            $data['thumbAvailable'] = $file->isThumbAvailable();

            yield $data;
        }
    }

    /**
     * @throws GetError
     */
    public function getSize(): int
    {
        $size = 0;

        foreach ($this->dirService->getFiles($this->html5MediaPath->getValue()) as $file) {
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
