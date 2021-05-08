<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service;

use Exception;
use GibsonOS\Core\Exception\DateTimeError;
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
    private GibsonStoreService $gibsonStoreService;

    private CoreFileService $coreFileService;

    private MediaRepository $mediaRepository;

    private PositionRepository $positionRepository;

    private DescriberFactory $describerFactory;

    public function __construct(
        GibsonStoreService $gibsonStoreService,
        CoreFileService $coreFileService,
        MediaRepository $mediaRepository,
        PositionRepository $positionRepository,
        DescriberFactory $describerFactory
    ) {
        $this->gibsonStoreService = $gibsonStoreService;
        $this->coreFileService = $coreFileService;
        $this->mediaRepository = $mediaRepository;
        $this->describerFactory = $describerFactory;
        $this->positionRepository = $positionRepository;
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws ReadError
     * @throws DateTimeError
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

        try {
            $metaInfos = $this->gibsonStoreService->getFileMetas($path);
        } catch (ExecuteError $e) {
            $metaInfos = [];
        }

        try {
            $media = $this->mediaRepository->getByDirAndFilename($dir, $filename);
            $html5Status = $media->getStatus();
            $html5Token = $media->getToken();

            if ($userId !== null) {
                $position = $this->positionRepository->getByMediaAndUserId(
                    $media->getId() ?? 0,
                    $userId
                )->getPosition();
            }
        } catch (SelectError $e) {
            // do nothing
        }

        $fileTypeDescriber = $this->describerFactory->create($path);

        $file = (new File(
            $filename,
            $this->coreFileService->getFileEnding($path),
            $fileSize,
            $fileTypeDescriber->getCategory()
        ))
            ->setThumbAvailable($fileTypeDescriber->isImageAvailable())
            ->setHtml5VideoStatus($html5Status)
            ->setHtml5VideoToken($html5Token)
            ->setPosition($position)
            ->setAccessed(fileatime($path))
            ->setModified(filemtime($path))
            ->setMetaInfos($metaInfos)
        ;

        return $file;
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
                in_array($path, $overwrite) ||
                in_array($path, $ignore)
            ) {
                return true;
            }

            throw new OverwriteException($path, $overwrite, $ignore);
        }

        return is_writable($this->coreFileService->getDir($path));
    }
}
