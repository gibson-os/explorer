<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;

class TrashController extends AbstractController
{
    public function read(): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);
    }

    public function delete(): AjaxResponse
    {
        $this->checkPermission(PermissionService::DELETE);
    }

    public function restore(): AjaxResponse
    {
        $this->checkPermission(PermissionService::WRITE);
    }
}