<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use Exception;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\FileExistsError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\CreateError as ImageCreateError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Service\DirService as CoreDirService;
use GibsonOS\Core\Service\FileService as CoreFileService;
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\FileResponse;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Module\Explorer\Attribute\CheckExplorerPermission;
use GibsonOS\Module\Explorer\Exception\OverwriteException;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Factory\File\TypeFactory;
use GibsonOS\Module\Explorer\Service\DirService;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Module\Explorer\Service\FileService;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\TrashService;
use JsonException;
use ReflectionException;

class FileController extends AbstractController
{
    /**
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws JsonException
     * @throws SaveError
     * @throws SelectError
     * @throws SetError
     * @throws ReflectionException
     */
    #[CheckExplorerPermission([Permission::DELETE])]
    public function delete(
        TrashService $trashService,
        string $dir,
        array $files = [],
    ): AjaxResponse {
        $trashService->add($dir, $files);

        return $this->returnSuccess();
    }

    #[CheckPermission([Permission::READ])]
    public function getDownload(
        RequestService $requestService,
        #[GetSetting('home_path')] Setting $homePath,
    ): ResponseInterface {
        $filename = '/' . urldecode($requestService->getQueryString());

        if (mb_strpos($homePath->getValue(), $filename) === 0) {
            return $this->returnFailure('Access denied', HttpStatusCode::FORBIDDEN);
        }

        return new FileResponse($requestService, $filename);
    }

    #[CheckPermission([Permission::READ])]
    public function getShow(
        RequestService $requestService,
        #[GetSetting('home_path')] Setting $homePath,
    ): ResponseInterface {
        $filename = '/' . urldecode($requestService->getQueryString());

        if (mb_strpos($homePath->getValue(), $filename) === 0) {
            return $this->returnFailure('Access denied', HttpStatusCode::FORBIDDEN);
        }

        return (new FileResponse($requestService, $filename))
            ->setDisposition('inline')
        ;
    }

    /**
     * @throws OverwriteException
     */
    #[CheckExplorerPermission([Permission::WRITE])]
    public function postUpload(
        CoreDirService $dirService,
        FileService $fileService,
        string $dir,
        ?array $file,
        ?string $filename,
        array $overwrite = [],
        array $ignore = [],
        bool $overwriteAll = false,
        bool $ignoreAll = false,
    ): AjaxResponse {
        $dir = $dirService->addEndSlash($dir);

        if (is_array($file)) {
            if (!is_string($file['tmp_name'])) {
                return $this->returnFailure('Uploaded file not found', HttpStatusCode::NOT_FOUND);
            }

            // $fileService->move($file['tmp_name'], $path, $overwrite, $ignore);
            // $fileService->setPerms($path, 0660);
        } elseif (!$fileService->isWritable($dir . $filename, $overwrite, $ignore)) {
            // $this->_Helper->isWritable($dir . $filename, $overwrite, $ignore);
            // @todo exception erstellen. Alternativ die alte methode in den explorer file service ziehen?
        }

        // return $this->returnSuccess($this->_Helper->getItem($path));
        return $this->returnSuccess();
    }

    /**
     * @throws GetError
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws SetError
     */
    #[CheckExplorerPermission([Permission::WRITE, Permission::DELETE], ['from', 'to'])]
    public function postMove(
        CoreFileService $fileService,
        CoreDirService $dirService,
        string $from,
        string $to,
        array $names,
    ): AjaxResponse {
        $from = $dirService->addEndSlash($from);
        $to = $dirService->addEndSlash($to);

        foreach ($names as $name) {
            $fileService->move($from . $name, $to . $name);
        }

        return $this->returnSuccess();
    }

    /**
     * @throws GetError
     * @throws CreateError
     * @throws SetError
     */
    #[CheckExplorerPermission([Permission::WRITE, Permission::DELETE], ['from', 'to'])]
    public function postCopy(
        CoreFileService $fileService,
        CoreDirService $dirService,
        string $from,
        string $to,
        array $names,
    ): AjaxResponse {
        $from = $dirService->addEndSlash($from);
        $to = $dirService->addEndSlash($to);

        foreach ($names as $name) {
            $fileService->copy($from . $name, $to . $name);
        }

        return $this->returnSuccess();
    }

    /**
     * @throws CreateError
     * @throws DeleteError
     * @throws FactoryError
     * @throws FileNotFound
     * @throws GetError
     * @throws ReadError
     * @throws SetError
     */
    #[CheckExplorerPermission([Permission::WRITE])]
    public function postRename(
        CoreFileService $coreFileService,
        FileService $fileService,
        CoreDirService $coreDirService,
        DirService $dirService,
        string $dir,
        string $oldFilename,
        string $newFilename,
    ): AjaxResponse {
        $dir = $coreDirService->addEndSlash($dir);
        $path = $dir . $newFilename;
        $coreFileService->move($dir . $oldFilename, $path);

        if (is_dir($path)) {
            return $this->returnSuccess($dirService->get($path));
        }

        return $this->returnSuccess($fileService->get($path));
    }

    /**
     * @throws CreateError
     * @throws FactoryError
     * @throws FileExistsError
     * @throws ReadError
     */
    #[CheckExplorerPermission([Permission::WRITE])]
    public function postAdd(
        CoreFileService $coreFileService,
        FileService $fileService,
        string $dir,
        string $filename,
    ): AjaxResponse {
        $path = $dir . $filename;

        $coreFileService->save($path, '');

        return $this->returnSuccess($fileService->get($path, $this->sessionService->getUserId()));
    }

    /**
     * @throws ExecuteError
     * @throws FactoryError
     * @throws FileNotFound
     * @throws GetError
     * @throws ReadError
     * @throws WriteError
     * @throws ImageCreateError
     * @throws LoadError
     * @throws Exception
     */
    #[CheckPermission([Permission::READ])]
    public function getImage(
        CoreDirService $dirService,
        GibsonStoreService $gibsonStoreService,
        ImageService $imageService,
        TypeFactory $typeFactory,
        string $dir,
        string $filename,
        int $width = null,
        int $height = null,
        bool $base64 = false,
    ): ResponseInterface {
        $path = $dirService->addEndSlash($dir) . $filename;

        if (!$gibsonStoreService->hasFileImage($path)) {
            $fileTypeService = $typeFactory->create($path);
            $image = $fileTypeService->getImage($path);
            $gibsonStoreService->setFileImage($path, $image);
        }

        $image = $gibsonStoreService->getFileImage($path, $width, $height);
        $body = $imageService->getString($image);

        if ($base64) {
            $body = base64_encode($body);
        }

        return new Response(
            $body,
            HttpStatusCode::OK,
            [
                'Pragma' => 'public',
                'Expires' => 0,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
                'Content-Type' => 'image/jpg',
                'Content-Length' => strlen($body),
                'Content-Transfer-Encoding' => $base64 ? 'base64' : 'binary',
                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
            ]
        );
    }

    /**
     * @throws ExecuteError
     * @throws FactoryError
     * @throws GetError
     * @throws ReadError
     * @throws WriteError
     * @throws JsonException
     * @throws Exception
     */
    #[CheckExplorerPermission([Permission::WRITE], ['path'])]
    public function getMetaInfos(
        ServiceManager $serviceManager,
        DescriberFactory $describerFactory,
        GibsonStoreService $gibsonStoreService,
        string $path
    ): AjaxResponse {
        $fileTypeDescriber = $describerFactory->create($path);

        if ($gibsonStoreService->hasFileMetas($path, $fileTypeDescriber->getMetasStructure())) {
            return $this->returnSuccess($gibsonStoreService->getFileMetas($path));
        }

        /** @var FileTypeInterface $fileTypeService */
        $fileTypeService = $serviceManager->get($fileTypeDescriber->getServiceClassname());
        $checkSum = md5_file($path);
        $fileMetas = $fileTypeService->getMetas($path);
        $gibsonStoreService->setFileMetas($path, $fileMetas, $checkSum ?: null);

        return $this->returnSuccess($fileMetas);
    }
}
