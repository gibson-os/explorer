<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service;

use Exception;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Service\FileService as CoreFileService;
use GibsonOS\Module\Explorer\Dto\File;
use GibsonOS\Module\Explorer\Exception\OverwriteException;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Repository\Html5\Media\PositionRepository;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;

class FileService
{
    public function __construct(private GibsonStoreService $gibsonStoreService, private CoreFileService $coreFileService, private MediaRepository $mediaRepository, private PositionRepository $positionRepository, private DescriberFactory $describerFactory)
    {
    }

    /**
     * @throws FactoryError
     * @throws ReadError
     */
    public function get(string $path, int $userId = null): File
    {
        $dir = $this->coreFileService->getDir($path);
        $filename = $this->coreFileService->getFilename($path);

        if (!mb_check_encoding($path)) {
            $newFilename = iconv('ISO-8859-15', 'UTF-8', $path) ?: '';

            if (rename($path, $newFilename)) {
                $path = $newFilename;
            }
        }

        $fileSize = filesize($path);
        $html5Status = null;
        $html5Token = null;
        $position = null;
        $positions = [];

        try {
            $metaInfos = $this->gibsonStoreService->getFileMetas($path);
        } catch (ExecuteError) {
            $metaInfos = [];
        }

        try {
            $media = $this->mediaRepository->getByDirAndFilename($dir, $filename);
            $mediaId = $media->getId() ?? 0;
            $html5Status = $media->getStatus();
            $html5Token = $media->getToken();

            if ($userId !== null) {
                $position = $this->positionRepository->getByMediaAndUserId($mediaId, $userId)->getPosition();
                $positions = $this->positionRepository->getByMediaAndConnectedUserId($mediaId, $userId);
            }
        } catch (SelectError) {
            // do nothing
        }

        $fileTypeDescriber = $this->describerFactory->create($path);

        return (new File(
            $filename,
            $this->coreFileService->getFileEnding($path),
            $fileSize,
            $fileTypeDescriber->getCategory()
        ))
            ->setThumbAvailable($fileTypeDescriber->isImageAvailable())
            ->setHtml5MediaStatus($html5Status)
            ->setHtml5MediaToken($html5Token)
            ->setPosition($position)
            ->setPositions($positions)
            ->setAccessed(fileatime($path))
            ->setModified(filemtime($path))
            ->setMetaInfos($metaInfos)
        ;
    }

    /**
     * @throws ExecuteError
     * @throws GetError
     * @throws WriteError
     * @throws Exception
     */
    public function setFileMetas(FileTypeInterface $fileTypeService, string $path): void
    {
        $checkSum = md5_file($path);
        $this->gibsonStoreService->setFileMetas($path, $fileTypeService->getMetas($path), $checkSum ?: null);
    }

    /**
     * @throws OverwriteException
     */
    public function isWritable(
        string $path,
        array $overwrite = [],
        array $ignore = []
    ): bool {
        if (file_exists($path)) {
            if (!is_writable($path)) {
                return false;
            }

            if (
                in_array($path, $overwrite)
                || in_array($path, $ignore)
            ) {
                return true;
            }

            throw new OverwriteException($path, $overwrite, $ignore);
        }

        return is_writable($this->coreFileService->getDir($path));
    }
}
