<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Module\Explorer\Service\TrashService;
use GibsonOS\Module\Explorer\Store\TrashStore;

class TrashController extends AbstractController
{
    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function read(TrashStore $trashStore, int $start = 0, int $limit = 25, array $sort = []): AjaxResponse
    {
        $trashStore->setLimit($limit, $start);
        $trashStore->setSortByExt($sort);

        return $this->returnSuccess($trashStore->getList(), $trashStore->getCount());
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ModelDeleteError
     * @throws SelectError
     */
    #[CheckPermission(Permission::DELETE)]
    public function delete(TrashService $trashService, SessionService $sessionService, array $tokens): AjaxResponse
    {
        $trashService->delete($tokens, $sessionService->getUserId());

        return $this->returnSuccess();
    }

    /**
     * @param string[] $tokens
     *
     * @throws CreateError
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ModelDeleteError
     * @throws SelectError
     * @throws SetError
     */
    #[CheckPermission(Permission::WRITE)]
    public function restore(TrashService $trashService, SessionService $sessionService, array $tokens): AjaxResponse
    {
        $trashService->restore($tokens, $sessionService->getUserId());

        return $this->returnSuccess();
    }
}
