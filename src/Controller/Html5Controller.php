<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use Exception;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\CreateError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\FileResponse;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Service\Response\TwigResponse;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Explorer\Factory\File\TypeFactory;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use GibsonOS\Module\Explorer\Store\Html5\MediaStore;
use GibsonOS\Module\Explorer\Store\Html5\ToSeeStore;

class Html5Controller extends AbstractController
{
    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function index(
        RequestService $requestService,
        SettingRepository $settingRepository,
        MediaStore $mediaStore,
        int $start = 0,
        int $limit = 100,
        array $sort = []
    ): AjaxResponse {
        $this->checkPermission(PermissionService::READ);

        $settingModels = $settingRepository->getAllByModuleName(
            $requestService->getModuleName(),
            $this->sessionService->getUserId() ?? 0
        );
        $settings = [];

        foreach ($settingModels as $setting) {
            if (mb_strpos($setting->getKey(), 'html5_') !== 0) {
                continue;
            }

            $settings[$setting->getKey()] = $setting->getValue();
        }

        $mediaStore->setLimit($limit, $start);
        $mediaStore->setSortByExt($sort);

        return new AjaxResponse([
            'data' => $mediaStore->getList(),
            'total' => $mediaStore->getCount(),
            'settings' => $settings,
            'size' => $mediaStore->getSize(),
        ]);
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SaveError
     * @throws SelectError
     */
    public function convert(
        SettingRepository $settingRepository,
        MediaService $mediaService,
        string $dir,
        array $files = [],
        string $audioStream = null,
        string $subtitleStream = null
    ): AjaxResponse {
        $this->checkPermission(PermissionService::WRITE + PermissionService::MANAGE);

        $userId = $this->sessionService->getUserId() ?? 0;
        $homPath = $settingRepository->getByKeyAndModuleName(
            $this->requestService->getModuleName(),
            $userId,
            'home_path'
        );

        if (mb_strpos($homPath->getValue(), $dir) === 0) {
            $this->returnFailure(
                sprintf('Zugriff auf das Verzeichnis %s ist nicht gestattet!', $dir),
                StatusCode::FORBIDDEN
            );
        }

        return $this->returnSuccess(
            $mediaService->scheduleConvert($userId, $dir, $files, $audioStream, $subtitleStream)
        );
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     * @throws ConvertStatusError
     * @throws FileNotFound
     * @throws OpenError
     * @throws ProcessError
     * @throws SetError
     */
    public function convertStatus(
        MediaService $mediaService,
        MediaRepository $mediaRepository,
        string $token
    ): AjaxResponse {
        $this->checkPermission(PermissionService::READ);

        return $this->returnSuccess($mediaService->getConvertStatus($mediaRepository->getByToken($token)));
    }

    /**
     * @throws DateTimeError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function video(SettingRepository $settingRepository, string $token): FileResponse
    {
        $this->checkPermission(PermissionService::READ);

        return (new FileResponse(
            $this->requestService,
            $settingRepository->getByKeyAndModuleName(
                $this->requestService->getModuleName(),
                $this->sessionService->getUserId() ?? 0,
                'html5_media_path'
            )->getValue() . $token . '.mp4'
        ))
            ->setType('video/mp4')
            ->setDisposition(null)
        ;
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

    /**
     * @throws LoginRequired
     * @throws PermissionDenied
     */
    public function chromecast(): TwigResponse
    {
        $this->checkPermission(PermissionService::READ);

        return $this->renderTemplate('@explorer/chromecast.html.twig');
    }

    /**
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws FileNotFound
     * @throws GetError
     * @throws LoginRequired
     * @throws OpenError
     * @throws PermissionDenied
     * @throws ProcessError
     * @throws SetError
     * @throws ExecuteError
     * @throws ReadError
     */
    public function toSeeList(ToSeeStore $toSeeStore): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        return $this->returnSuccess($toSeeStore->getList(), $toSeeStore->getCount());
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws SelectError
     */
    public function get(MediaRepository $mediaRepository, string $token): AjaxResponse
    {
        $this->checkPermission(PermissionService::READ);

        return $this->returnSuccess($mediaRepository->getByToken($token));
    }

    /**
     * @throws DateTimeError
     * @throws ExecuteError
     * @throws FileNotFound
     * @throws GetError
     * @throws LoginRequired
     * @throws PermissionDenied
     * @throws ReadError
     * @throws SelectError
     * @throws CreateError
     * @throws LoadError
     * @throws WriteError
     * @throws FactoryError
     * @throws Exception
     */
    public function image(
        MediaRepository $mediaRepository,
        GibsonStoreService $gibsonStoreService,
        ImageService $imageService,
        TypeFactory $typeFactory,
        string $token,
        int $width = null,
        int $height = null
    ): Response {
        $this->checkPermission(PermissionService::READ);

        $media = $mediaRepository->getByToken($token);
        $path = $media->getDir() . $media->getFilename();

        if (!$gibsonStoreService->hasFileImage($path)) {
            $fileTypeService = $typeFactory->create($path);
            $image = $fileTypeService->getImage($path);
            $gibsonStoreService->setFileImage($path, $image);
        }

        $image = $gibsonStoreService->getFileImage($path, $width, $height);
        $body = $imageService->getString($image, 'jpg');

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
}
