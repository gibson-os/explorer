<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Module\Explorer\Service\TrashService;
use GibsonOS\Module\Explorer\Store\TrashStore;

class TrashController extends AbstractController
{
    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws GetError
     */
    public function read(TrashStore $trashStore, int $start = 0, int $limit = 25, array $sort = []): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        $trashStore->setLimit($limit, $start);
        $trashStore->setSortByExt($sort);

        return $this->returnSuccess([...$trashStore->getList()], $trashStore->getCount());
    }

    /**
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws LoginRequired
     * @throws ModelDeleteError
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function delete(TrashService $trashService, SessionService $sessionService, array $tokens): AjaxResponse
    {
        $this->checkPermission(PermissionService::DELETE);

        $trashService->delete($tokens, $sessionService->getUserId());

        return $this->returnSuccess();
    }

    /**
     * @param string[] $tokens
     * @throws CreateError
     * @throws DateTimeError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws LoginRequired
     * @throws ModelDeleteError
     * @throws PermissionDenied
     * @throws SelectError
     * @throws SetError
     */
    public function restore(TrashService $trashService, SessionService $sessionService, array $tokens): AjaxResponse
    {
        $this->checkPermission(PermissionService::WRITE);

        $trashService->restore($tokens, $sessionService->getUserId());

        return $this->returnSuccess();
    }
}