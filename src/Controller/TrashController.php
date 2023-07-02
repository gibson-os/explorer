<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\DeleteError as ModelDeleteError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\SessionService;
use GibsonOS\Module\Explorer\Service\TrashService;
use GibsonOS\Module\Explorer\Store\TrashStore;
use JsonException;

class TrashController extends AbstractController
{
    /**
     * @throws SelectError
     */
    #[CheckPermission([Permission::READ])]
    public function get(TrashStore $trashStore, int $start = 0, int $limit = 25, array $sort = []): AjaxResponse
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
     * @throws JsonException
     */
    #[CheckPermission([Permission::DELETE])]
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
     * @throws JsonException
     */
    #[CheckPermission([Permission::WRITE])]
    public function postRestore(TrashService $trashService, SessionService $sessionService, array $tokens): AjaxResponse
    {
        $trashService->restore($tokens, $sessionService->getUserId());

        return $this->returnSuccess();
    }
}
