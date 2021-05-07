<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService as CoreDirService;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Module\Explorer\Service\DirService;
use GibsonOS\Module\Explorer\Store\DirListStore;
use GibsonOS\Module\Explorer\Store\DirStore;

class DirController extends AbstractController
{
    /**
     * @throws DateTimeError
     * @throws FactoryError
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws ReadError
     * @throws SelectError
     */
    public function read(?string $dir, SettingRepository $settingRepository, DirStore $dirStore): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        $homePath = $settingRepository->getByKeyAndModuleName(
            'explorer',
            $this->sessionService->getUserId() ?? 0,
            'home_path'
        )->getValue();

        if (empty($dir) || mb_strpos($dir, $homePath) !== 0) {
            $dir = $homePath;
        }

        $dirStore
            ->setDir($dir)
            ->setUserId($this->sessionService->getUserId())
        ;

        return new AjaxResponse([
            'success' => true,
            'failure' => false,
            'data' => $dirStore->getList(),
            'total' => $dirStore->getCount(),
            'dir' => $dir,
            'meta' => $dirStore->getMetas(),
            'path' => explode(DIRECTORY_SEPARATOR, mb_substr($dir, 0, -1)),
            'homePath' => $homePath,
        ]);
    }

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function dirList(
        DirListStore $dirListStore,
        SettingRepository $settingRepository,
        ?string $node,
        ?string $dir
    ): AjaxResponse {
        $this->checkPermission(PermissionService::READ);

        $homePath = $settingRepository->getByKeyAndModuleName(
            'explorer',
            $this->sessionService->getUserId() ?? 0,
            'home_path'
        )->getValue();

        $withParents = true;

        if (!empty($node) && $node !== 'root') {
            $dir = $node;
            $withParents = false;
        }

        $dirListStore
            ->setHomePath($homePath)
            ->setDir($dir ?? DIRECTORY_SEPARATOR)
            ->setWithParents($withParents)
        ;

        return $this->returnSuccess($dirListStore->getList());
    }

    /**
     * @throws CreateError
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws ReadError
     */
    public function add(DirService $dirService, CoreDirService $coreDirService, string $dir, string $dirname): AjaxResponse
    {
        $this->checkPermission(PermissionService::WRITE);

        $path = $coreDirService->addEndSlash($dir) . $dirname;
        $coreDirService->create($path);

        return $this->returnSuccess($dirService->get($path));
    }
}
