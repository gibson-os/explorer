<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Archive\ZipArchive;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\ArchiveException;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Service\DirService as CoreDirService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\FileResponse;
use GibsonOS\Module\Explorer\Attribute\CheckExplorerPermission;
use GibsonOS\Module\Explorer\Service\DirService;
use GibsonOS\Module\Explorer\Store\DirListStore;
use GibsonOS\Module\Explorer\Store\DirStore;

class DirController extends AbstractController
{
    /**
     * @throws DateTimeError
     * @throws FactoryError
     * @throws GetError
     * @throws ReadError
     */
    #[CheckExplorerPermission([Permission::READ])]
    public function get(
        DirStore $dirStore,
        CoreDirService $dirService,
        string $homePath,
        ?string $dir,
    ): AjaxResponse {
        $dir ??= $homePath;
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
            'path' => explode(DIRECTORY_SEPARATOR, $dirService->removeEndSlash($dir)),
            'homePath' => $homePath,
        ]);
    }

    /**
     * @throws GetError
     * @throws ReadError
     */
    #[CheckPermission([Permission::READ])]
    public function getList(
        DirListStore $dirListStore,
        #[GetSetting('home_path')] Setting $homePath,
        ?string $node,
        ?string $dir,
    ): AjaxResponse {
        $withParents = true;

        if (!empty($node) && $node !== 'root') {
            $dir = $node;
            $withParents = false;
        }

        $dirListStore
            ->setHomePath($homePath->getValue())
            ->setDir($dir ?: DIRECTORY_SEPARATOR)
            ->setWithParents($withParents)
        ;

        return $this->returnSuccess($dirListStore->getList());
    }

    /**
     * @throws CreateError
     * @throws GetError
     * @throws ReadError
     */
    #[CheckExplorerPermission([Permission::READ])]
    public function post(
        DirService $dirService,
        CoreDirService $coreDirService,
        string $dir,
        string $dirname,
    ): AjaxResponse {
        $dir = $coreDirService->addEndSlash($dir);

        $path = $dir . $dirname;
        $coreDirService->create($path);

        return $this->returnSuccess($dirService->get($path));
    }

    /**
     * @throws GetError
     * @throws ArchiveException
     */
    #[CheckExplorerPermission([Permission::READ])]
    public function getDownload(
        CoreDirService $dirService,
        CoreDirService $coreDirService,
        ZipArchive $zipArchive,
        RequestService $requestService,
        string $dir,
    ): FileResponse|AjaxResponse {
        $dir = $coreDirService->addEndSlash($dir);

        $fileName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5((string) rand()) . '.zip';

        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $zipArchive->packFiles($fileName, $dirService->getFiles($dir));

        return new FileResponse($requestService, $fileName);
    }
}
