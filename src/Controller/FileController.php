<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use Exception;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\FileExistsError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\CreateError as ImageCreateError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService as CoreFileService;
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\FileResponse;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Service\TwigService;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Explorer\Exception\OverwriteException;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Factory\File\TypeFactory;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;
use GibsonOS\Module\Explorer\Service\FileService;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\TrashService;

class FileController extends AbstractController
{
    private SettingRepository $settingRepository;

    public function __construct(
        PermissionService $permissionService,
        RequestService $requestService,
        TwigService $twigService,
        SessionService $sessionService,
        SettingRepository $settingRepository
    ) {
        parent::__construct($permissionService, $requestService, $twigService, $sessionService);
        $this->settingRepository = $settingRepository;
    }

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     * @throws SaveError
     */
    public function delete(
        TrashService $trashService,
        string $dir,
        array $files
    ): AjaxResponse {
        $this->checkPermission(PermissionService::DELETE);

        if (mb_strpos($this->getHomePath(), $dir) === 0) {
            return $this->returnFailure('Access denied', StatusCode::FORBIDDEN);
        }

        $trashService->add($dir, $files);

        return $this->returnSuccess();
    }

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function download(RequestService $requestService): ResponseInterface
    {
        $this->checkPermission(PermissionService::READ);

        $filename = '/' . urldecode($requestService->getQueryString());

        if (mb_strpos($this->getHomePath(), $filename) === 0) {
            return $this->returnFailure('Access denied', StatusCode::FORBIDDEN);
        }

        return new FileResponse($requestService, $filename);
    }

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function show(RequestService $requestService): ResponseInterface
    {
        $this->checkPermission(PermissionService::READ);

        $filename = '/' . urldecode($requestService->getQueryString());

        if (mb_strpos($this->getHomePath(), $filename) === 0) {
            return $this->returnFailure('Access denied', StatusCode::FORBIDDEN);
        }

        return (new FileResponse($requestService, $filename))
            ->setDisposition('inline')
        ;
    }

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     * @throws OverwriteException
     */
    public function upload(
        DirService $dirService,
        FileService $fileService,
        string $dir,
        ?array $file,
        ?string $filename,
        array $overwrite = [],
        array $ignore = [],
        bool $overwriteAll = false,
        bool $ignoreAll = false
    ): AjaxResponse {
        $this->checkPermission(PermissionService::WRITE);

        $dir = $dirService->addEndSlash($dir);
        //$path = $dir . $file['name'];

        if (mb_strpos($this->getHomePath(), $dir) === 0) {
            return $this->returnFailure('Access denied', StatusCode::FORBIDDEN);
        }

        if (is_array($file)) {
            if (!is_string($file['tmp_name'])) {
                return $this->returnFailure('Uploaded file not found', StatusCode::NOT_FOUND);
            }

            //$fileService->move($file['tmp_name'], $path, $overwrite, $ignore);
            //$fileService->setPerms($path, 0660);
        } elseif (!$fileService->isWritable($dir . $filename, $overwrite, $ignore)) {
            // $this->_Helper->isWritable($dir . $filename, $overwrite, $ignore);
            // @todo exception erstellen. Alternativ die alte methode in den explorer file service ziehen?
        }

        //return $this->returnSuccess($this->_Helper->getItem($path));
        return $this->returnSuccess();
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws SetError
     */
    public function move(CoreFileService $fileService, string $from, string $to, string $name): AjaxResponse
    {
        $this->checkPermission(PermissionService::WRITE + PermissionService::DELETE);

        $homePath = $this->getHomePath();

        if (
            mb_strpos($homePath, $from) === 0 ||
            mb_strpos($homePath, $to) === 0
        ) {
            return $this->returnFailure('Access denied', StatusCode::FORBIDDEN);
        }

        $fileService->move($from . $name, $to . $name);

        return $this->returnSuccess();
    }

    /**
     * @throws CreateError
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     * @throws SetError
     */
    public function rename(
        CoreFileService $fileService,
        DirService $dirService,
        string $dir,
        string $oldFilename,
        string $newFilename
    ): AjaxResponse {
        $this->checkPermission(PermissionService::WRITE);

        $dir = $dirService->addEndSlash($dir);

        if (mb_strpos($this->getHomePath(), $dir) === 0) {
            return $this->returnFailure('Access denied', StatusCode::FORBIDDEN);
        }

        $path = $dir . $newFilename;
        $fileService->move($dir . $oldFilename, $path);

        return $this->returnSuccess();
    }

    /**
     * @throws CreateError
     * @throws DateTimeError
     * @throws FactoryError
     * @throws FileExistsError
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws ReadError
     */
    public function add(
        CoreFileService $coreFileService,
        FileService $fileService,
        string $dir,
        string $filename
    ): AjaxResponse {
        $this->checkPermission(PermissionService::WRITE);

        $path = $dir . $filename;

        $coreFileService->save($path, null);

        return $this->returnSuccess($fileService->get($path, $this->sessionService->getUserId()));
    }

    /**
     * @throws ExecuteError
     * @throws FactoryError
     * @throws FileNotFound
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws ReadError
     * @throws WriteError
     * @throws ImageCreateError
     * @throws LoadError
     * @throws Exception
     */
    public function image(
        DirService $dirService,
        GibsonStoreService $gibsonStoreService,
        ImageService $imageService,
        TypeFactory $typeFactory,
        string $dir,
        string $filename,
        int $width = null,
        int $height = null
    ): ResponseInterface {
        $this->checkPermission(PermissionService::READ);

        $path = $dirService->addEndSlash($dir) . $filename;

        if (!$gibsonStoreService->hasFileImage($path)) {
            $fileTypeService = $typeFactory->create($path);
            $image = $fileTypeService->getImage($path);
            $gibsonStoreService->setFileImage($path, $image);
        }

        $image = $gibsonStoreService->getFileImage($path, $width, $height);
        $body = $imageService->getString($image);

        return new Response(
            $body,
            StatusCode::OK,
            [
                'Pragma' => 'public',
                'Expires' => 0,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
                'Content-Type' => 'image/jpg',
                'Content-Length' => strlen($body),
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
            ]
        );
    }

    /**
     * @throws FactoryError
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws ExecuteError
     * @throws ReadError
     * @throws WriteError
     */
    public function metaInfos(
        ServiceManagerService $serviceManagerService,
        DescriberFactory $describerFactory,
        GibsonStoreService $gibsonStoreService,
        string $path
    ): AjaxResponse {
        $this->checkPermission(PermissionService::WRITE);
        $fileTypeDescriber = $describerFactory->create($path);

        if ($gibsonStoreService->hasFileMetas($path, $fileTypeDescriber->getMetasStructure())) {
            return $this->returnSuccess($gibsonStoreService->getFileMetas($path));
        }

        /** @var FileTypeInterface $fileTypeService */
        $fileTypeService = $serviceManagerService->get($fileTypeDescriber->getServiceClassname());
        $checkSum = md5_file($path);
        $fileMetas = $fileTypeService->getMetas($path);
        $gibsonStoreService->setFileMetas($path, $fileMetas, $checkSum ?: null);

        return $this->returnSuccess($fileMetas);
    }

    /**
     * @throws DateTimeError
     * @throws SelectError
     */
    private function getHomePath(): string
    {
        return $this->settingRepository->getByKeyAndModuleName(
            'explorer',
            $this->sessionService->getUserId() ?? 0,
            'home_path'
        )->getValue();
    }
}
