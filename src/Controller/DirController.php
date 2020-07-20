<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Module\Explorer\Store\DirStore;

class DirController extends AbstractController
{
    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     * @throws FactoryError
     * @throws FileNotFound
     * @throws GetError
     * @throws ExecuteError
     * @throws ReadError
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

        $dirStore->setDir($dir);

        return new AjaxResponse([
            'data' => $dirStore->getList(),
            'total' => $dirStore->getCount(),
            'dir' => $dir,
            'meta' => $dirStore->getMetas(),
            'path' => explode(DIRECTORY_SEPARATOR, mb_substr($dir, 0, -1)),
            'homePath' => $homePath,
        ]);
    }
}
