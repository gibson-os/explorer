<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Command\Html5;

use DateTime;
use Exception;
use GibsonOS\Core\Command\AbstractCommand;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\FlockService;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;

class DeleteCommand extends AbstractCommand
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * @var FlockService
     */
    private $flockService;

    /**
     * @var DirService
     */
    private $dirService;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var string
     */
    private $mediaPath;

    public function __construct(
        MediaRepository $mediaRepository,
        MediaService $mediaService,
        SettingRepository $settingRepository,
        FlockService $flockService,
        DirService $dirService,
        FileService $fileService
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->mediaService = $mediaService;
        $this->settingRepository = $settingRepository;
        $this->flockService = $flockService;
        $this->dirService = $dirService;
        $this->fileService = $fileService;
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ModelDeleteError
     * @throws SelectError
     */
    protected function run(): int
    {
        $this->mediaPath = $this->settingRepository->getByKeyAndModuleName(
            'explorer',
            0,
            'html5_media_path'
        )->getValue();

        $this->deleteWhereFileNotExists();
        $this->deleteWhereMediaNotExists();
        $this->deleteWhereLifetimeExpired();
        $this->deleteWhereSizeExceeded();

        return 0;
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     * @throws ModelDeleteError
     */
    private function deleteWhereFileNotExists(): void
    {
        foreach ($this->mediaRepository->getAllByStatus(Media::STATUS_GENERATED) as $media) {
            if (file_exists($media->getDir() . $media->getFilename())) {
                continue;
            }

            $media->delete();
        }
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws GetError
     */
    private function deleteWhereMediaNotExists(): void
    {
        foreach ($this->dirService->getFiles($this->mediaPath) as $file) {
            $filename = $this->fileService->getFilename($file);
            $token = mb_substr($filename, 0, (int) mb_strpos($filename, '.'));

            try {
                $this->mediaRepository->getByToken($token);
            } catch (SelectError $e) {
                try {
                    $this->fileService->delete($this->mediaPath, $filename);
                } catch (FileNotFound $e) {
                    // File does not exists
                }
            }
        }
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ModelDeleteError
     * @throws Exception
     */
    private function deleteWhereLifetimeExpired(): void
    {
        try {
            $lifetime = $this->settingRepository->getByKeyAndModuleName(
                'explorer',
                0,
                'html5_media_lifetime'
            )->getValue();

            foreach ($this->mediaRepository->getAllOlderThan(new DateTime('-' . $lifetime . ' days')) as $media) {
                $this->fileService->delete($this->dirService->addEndSlash($media->getDir()) . $media->getFilename());
                $media->delete();
            }
        } catch (SelectError $e) {
            return;
        }
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ModelDeleteError
     */
    private function deleteWhereSizeExceeded(): void
    {
        try {
            $size = $this->settingRepository->getByKeyAndModuleName(
                'explorer',
                0,
                'html5_media_size'
            )->getValue();

            $hits = [];
            preg_match('/(\d+)(\w*)/', $size, $hits);
            $size = (int) $hits[1];

            if (array_key_exists(2, $hits)) {
                $types = [
                    'k' => 1024,
                    'kb' => 1024,
                    'm' => 1048576,
                    'mb' => 1048576,
                    'g' => 1073741824,
                    'gb' => 1073741824,
                ];

                $size *= $types[$hits[2]];
            }

            if (empty($size)) {
                return;
            }

            $dirSize = 0;

            foreach ($this->dirService->getFiles($this->mediaPath) as $file) {
                $dirSize += filesize($file);
            }

            if ($dirSize <= $size) {
                return;
            }

            $deleteSize = $dirSize - $size;

            foreach ($this->mediaRepository->getAllByStatus('generated') as $media) {
                if ($deleteSize <= 0) {
                    break;
                }

                $fileSize = filesize($this->mediaPath . $media->getToken() . '.mp4');
                $this->fileService->delete($this->dirService->addEndSlash($media->getDir()) . $media->getFilename());
                $media->delete();
                $deleteSize -= $fileSize;
            }
        } catch (SelectError $e) {
            return;
        }
    }
}
