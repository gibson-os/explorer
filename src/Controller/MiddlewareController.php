<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

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
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Explorer\Attribute\CheckChromecastPermission;
use GibsonOS\Module\Explorer\Exception\MediaException;
use GibsonOS\Module\Explorer\Factory\File\TypeFactory;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
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
    #[CheckChromecastPermission(Permission::READ)]
    public function toSeeList(
        ToSeeStore $toSeeStore,
        array $userIds,
    ): AjaxResponse {
        $toSeeStore->setUserIds($userIds);

        return $this->returnSuccess($toSeeStore->getList(), $toSeeStore->getCount());
    }

    /**
     * @throws MiddlewareException
     * @throws SaveError
     * @throws WebException
     * @throws \JsonException
     * @throws \ReflectionException
     */
    #[CheckChromecastPermission(Permission::WRITE)]
    public function savePosition(
        MediaService $mediaService,
        #[GetModel(['token' => 'token'])] Media $media,
        int $position,
        array $userIds,
    ): AjaxResponse {
        foreach ($userIds as $userId) {
            $mediaService->savePosition(
                $media,
                $position,
                $userId
            );
        }

        return $this->returnSuccess();
    }

    #[CheckChromecastPermission(Permission::READ)]
    public function image(
        GibsonStoreService $gibsonStoreService,
        ImageService $imageService,
        TypeFactory $typeFactory,
        #[GetModel(['token' => 'token'])] Media $media,
        int $width = null,
        int $height = null,
    ): ResponseInterface {
        $path = $media->getDir() . $media->getFilename();

        if (!$gibsonStoreService->hasFileImage($path)) {
            $fileTypeService = $typeFactory->create($path);
            $image = $fileTypeService->getImage($path);
            $gibsonStoreService->setFileImage($path, $image);
        }

        $image = $gibsonStoreService->getFileImage($path, $width, $height);
        $body = $imageService->getString($image);

        return new Response(
            $body,
            StatusCode::OK,
            [
                'Pragma' => 'public',
                'Expires' => 0,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
                'Content-Type' => 'image/jpg',
                'Content-Length' => strlen($body),
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
            ]
        );
    }

    #[CheckChromecastPermission(Permission::READ)]
    public function get(#[GetModel(['token' => 'token'])] Media $media): AjaxResponse
    {
        return $this->returnSuccess($media);
    }
}
