<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\MiddlewareException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\MiddlewareService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Explorer\Exception\MediaException;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use GibsonOS\Module\Explorer\Store\Html5\ToSeeStore;

class MiddlewareController extends AbstractController
{
    /**
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws FileNotFound
     * @throws MediaException
     * @throws MiddlewareException
     * @throws NoAudioError
     * @throws OpenError
     * @throws ProcessError
     * @throws ReadError
     * @throws SaveError
     * @throws SelectError
     * @throws SetError
     * @throws WebException
     * @throws \JsonException
     */
    #[CheckPermission(Permission::READ)]
    public function toSeeList(
        MiddlewareService $middlewareService,
        ToSeeStore $toSeeStore,
        string $sessionId,
    ): AjaxResponse {
        $response = $middlewareService->send('chromecast', 'getSessionUserIds', ['id' => $sessionId]);
        $toSeeStore->setUserIds(array_values(JsonUtility::decode($response->getBody()->getContent())['data']));

        return $this->returnSuccess($toSeeStore->getList(), $toSeeStore->getCount());
    }

    /**
     * @throws MiddlewareException
     * @throws SaveError
     * @throws WebException
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckPermission(Permission::WRITE)]
    public function savePosition(
        MiddlewareService $middlewareService,
        MediaService $mediaService,
        #[GetModel(['token' => 'token'])] Media $media,
        int $position,
        string $sessionId,
    ): AjaxResponse {
        $response = $middlewareService->send('chromecast', 'getSessionUserIds', ['id' => $sessionId]);

        foreach (JsonUtility::decode($response->getBody()->getContent())['data'] as $userId) {
            $mediaService->savePosition(
                $media,
                $position,
                $userId
            );
        }

        return $this->returnSuccess();
    }
}
