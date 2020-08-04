<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service;

use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Service\FileService as CoreFileService;
use GibsonOS\Module\Explorer\Dto\File;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;

class FileService
{
    /**
     * @var GibsonStoreService
     */
    private $gibsonStoreService;

    /**
     * @var CoreFileService
     */
    private $coreFileService;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var DescriberFactory
     */
    private $describerFactory;

    public function __construct(
        GibsonStoreService $gibsonStoreService,
        CoreFileService $coreFileService,
        MediaRepository $mediaRepository,
        DescriberFactory $describerFactory
    ) {
        $this->gibsonStoreService = $gibsonStoreService;
        $this->coreFileService = $coreFileService;
        $this->mediaRepository = $mediaRepository;
        $this->describerFactory = $describerFactory;
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws ReadError
     */
    public function get(string $path): File
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

        try {
            $media = $this->mediaRepository->getByDirAndFilename($dir, $filename);
            $html5Status = $media->getStatus();
            $html5Token = $media->getToken();
        } catch (SelectError $e) {
            $html5Status = null;
            $html5Token = null;
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
            ->setAccessed(fileatime($path))
            ->setModified(filemtime($path))
        ;

        try {
            $file->setMetaInfos($this->gibsonStoreService->getFileMetas($path) ?: []);
        } catch (ExecuteError $e) {
            // Store error
        }

        return $file;
    }
}
