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
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use GibsonOS\Module\Explorer\Utility\File\TypeUtility;

class Html5Controller extends AbstractController
{
    public function convert(
        ModuleRepository $moduleRepository,
        SettingRepository $settingRepository,
        MediaRepository $mediaRepository,
        TypeUtility $typeUtility,
        string $dir,
        array $files,
        string $audioStream = null,
        string $subtitleStream = null
    ): AjaxResponse {
        $this->checkPermission(PermissionService::WRITE + PermissionService::MANAGE);

        $userId = $this->sessionService->getUserId() ?? 0;
        $homPath = $settingRepository->getByKey(
            $moduleRepository->getByName($this->requestService->getModuleName())->getId() ?? 0,
            $userId,
            'home_path'
        );

        if (mb_strpos($homPath->getValue(), $dir) === 0) {
            $this->returnFailure(
                sprintf('Zugriff auf das Verzeichnis %s ist nicht gestattet!', $dir),
                StatusCode::FORBIDDEN
            );
        }

        foreach ($files as $file) {
            (new Media())
                ->setToken($mediaRepository->getFreeToken())
                ->setDir($dir)
                ->setFilename($file)
                ->setAudioStream($audioStream)
                ->setSubtitleStream($subtitleStream)
                ->setType($typeUtility->getCategory($file))
                ->setUserId($userId)
                ->save()
            ;
        }
    }

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

        $mediaService->savePosition(
            $mediaRepository->getByToken($token),
            $position,
            $this->sessionService->getUserId() ?? 1 // @todo der chromecast hat keine session und keine user id. oAuth?
        );

        return $this->returnSuccess();
    }
}
