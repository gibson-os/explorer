<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\TrashService;

class FileController extends AbstractController
{
    public function delete(
        TrashService $trashService,
        SettingRepository $settingRepository,
        string $dir,
        array $files
    ): AjaxResponse {
        $this->checkPermission(PermissionService::DELETE);

        $homePath = $settingRepository->getByKeyAndModuleName(
            'explorer',
            $this->sessionService->getUserId() ?? 0,
            'home_path'
        )->getValue();

        if (mb_strpos($homePath, $dir) === 0) {
            return $this->returnFailure('Access denied', StatusCode::FORBIDDEN);
        }

        $trashService->add($dir, $files);

        return $this->returnSuccess();
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
}
