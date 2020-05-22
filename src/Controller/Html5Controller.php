<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;

class Html5Controller extends AbstractController
{
    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws LoginRequired
     * @throws SaveError
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function savePosition(
        MediaService $mediaService,
        MediaRepository $mediaRepository,
        string $token,
        int $position
    ): AjaxResponse {
        $this->checkPermission(PermissionService::WRITE);

        $mediaService->savePosition($mediaRepository->getByToken($token), $position);

        return $this->returnSuccess();
    }
}
