<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use Exception;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\CreateError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\FileResponse;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Service\Response\TwigResponse;
use GibsonOS\Core\Utility\StatusCode;
use GibsonOS\Module\Explorer\Factory\File\TypeFactory;
use GibsonOS\Module\Explorer\Model\Html5\Media;
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
     * @throws SelectError
     */
    #[CheckPermission(Permission::READ)]
    public function index(
        RequestService $requestService,
        SettingRepository $settingRepository,
        MediaStore $mediaStore,
        int $start = 0,
        int $limit = 100,
        array $sort = []
    ): AjaxResponse {
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
            'success' => true,
            'failure' => false,
            'data' => [...$mediaStore->getList()],
            'total' => $mediaStore->getCount(),
            'settings' => $settings,
            'size' => $mediaStore->getSize(),
        ]);
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SaveError
     * @throws SelectError
     */
    #[CheckPermission(Permission::WRITE + Permission::MANAGE)]
    public function convert(
        #[GetSetting('home_path')] Setting $homePath,
        MediaService $mediaService,
        string $dir,
        array $files = [],
        string $audioStream = null,
        string $subtitleStream = null
    ): AjaxResponse {
        $userId = $this->sessionService->getUserId() ?? 0;

        if (mb_strpos($homePath->getValue(), $dir) === 0) {
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
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws FileNotFound
     * @throws OpenError
     * @throws ProcessError
     * @throws SetError
     * @throws NoAudioError
     */
    #[CheckPermission(Permission::READ)]
    public function convertStatus(
        MediaService $mediaService,
        #[GetModel(['token' => 'token'])] Media $media
    ): AjaxResponse {
        return $this->returnSuccess($mediaService->getConvertStatus($media));
    }

    #[CheckPermission(Permission::READ)]
    public function video(
        DirService $dirService,
        #[GetSetting('html5_media_path')] Setting $html5MediaPath,
        #[GetModel(['token' => 'token'])] Media $media
    ): FileResponse {
        return $this->stream(
            $dirService,
            $html5MediaPath,
            $media,
            'mp4',
            'video/mp4'
        );
    }

    #[CheckPermission(Permission::READ)]
    public function audio(
        DirService $dirService,
        #[GetSetting('html5_media_path')] Setting $html5MediaPath,
        #[GetModel(['token' => 'token'])] Media $media
    ): FileResponse {
        return $this->stream(
            $dirService,
            $html5MediaPath,
            $media,
            'mp3',
            'audio/mp3'
        );
    }

    /**
     * @param int[] $userIds
     *
     * @throws DateTimeError
     * @throws SaveError
     */
    #[CheckPermission(Permission::WRITE)]
    public function savePosition(
        MediaService $mediaService,
        #[GetModel(['token' => 'token'])] Media $media,
        int $position,
        array $userIds
    ): AjaxResponse {
        foreach (array_unique($userIds) as $userId) {
            $mediaService->savePosition(
                $media,
                $position,
                $userId
            );
        }

        return $this->returnSuccess();
    }

    #[CheckPermission(Permission::READ)]
    public function chromecast(): TwigResponse
    {
        return $this->renderTemplate('@explorer/chromecast.html.twig');
    }

    /**
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws FileNotFound
     * @throws NoAudioError
     * @throws OpenError
     * @throws ProcessError
     * @throws ReadError
     * @throws SelectError
     * @throws SetError
     */
    #[CheckPermission(Permission::READ)]
    public function toSeeList(ToSeeStore $toSeeStore, ?array $userIds): AjaxResponse
    {
        $toSeeStore->setUserIds(array_values(array_unique($userIds ?: [$this->sessionService->getUserId() ?? 0])));

        return $this->returnSuccess($toSeeStore->getList(), $toSeeStore->getCount());
    }

    #[CheckPermission(Permission::READ)]
    public function get(#[GetModel(['token' => 'token'])] Media $media): AjaxResponse
    {
        return $this->returnSuccess($media);
    }

    /**
     * @throws ExecuteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ReadError
     * @throws SelectError
     * @throws CreateError
     * @throws LoadError
     * @throws WriteError
     * @throws FactoryError
     * @throws Exception
     */
    #[CheckPermission(Permission::READ)]
    public function image(
        GibsonStoreService $gibsonStoreService,
        ImageService $imageService,
        TypeFactory $typeFactory,
        #[GetModel(['token' => 'token'])] Media $media,
        int $width = null,
        int $height = null
    ): Response {
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

    /**
     * @throws SelectError
     * @throws DeleteError
     */
    #[CheckPermission(Permission::DELETE)]
    public function delete(MediaRepository $mediaRepository, array $tokens): AjaxResponse
    {
        foreach ($mediaRepository->getByTokens($tokens) as $media) {
            $media->delete();
        }

        return $this->returnSuccess();
    }

    private function stream(
        DirService $dirService,
        Setting $htmlMediaPath,
        Media $media,
        string $fileEnding,
        string $type
    ): FileResponse {
        $filename = $dirService->addEndSlash($media->getDir()) . $media->getFilename();

        if ($media->isGenerationRequired()) {
            $filename = $htmlMediaPath->getValue() . $media->getToken() . '.' . $fileEnding;
        }

        return (new FileResponse($this->requestService, $filename))
            ->setType($type)
            ->setDisposition(null)
        ;
    }
}
