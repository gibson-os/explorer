<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\FileResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Core\Service\TwigService;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\TrashService;

class FileController extends AbstractController
{
    /**
     * @var SettingRepository
     */
    private $settingRepository;

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
