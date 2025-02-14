<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use Exception;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Core\Attribute\GetMappedModel;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Attribute\GetModels;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Dto\Form\ModelFormConfig;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\FormException;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\CreateError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Exception\MiddlewareException;
use GibsonOS\Core\Exception\Model\DeleteError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ModelManager;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Repository\ModuleRepository;
use GibsonOS\Core\Repository\SettingRepository;
use GibsonOS\Core\Service\File\TypeService;
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Core\Service\MiddlewareService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\FileResponse;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Core\Wrapper\ModelWrapper;
use GibsonOS\Module\Explorer\Attribute\CheckDeviceRequestValuePermission;
use GibsonOS\Module\Explorer\Attribute\CheckExplorerPermission;
use GibsonOS\Module\Explorer\Exception\MediaException;
use GibsonOS\Module\Explorer\Factory\File\TypeFactory;
use GibsonOS\Module\Explorer\Form\Html5\ConnectedUserForm;
use GibsonOS\Module\Explorer\Model\Html5\ConnectedUser;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\Media\PositionRepository;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use GibsonOS\Module\Explorer\Store\Html5\ConnectedUserStore;
use GibsonOS\Module\Explorer\Store\Html5\MediaStore;
use GibsonOS\Module\Explorer\Store\Html5\ToSeeStore;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class Html5Controller extends AbstractController
{
    /**
     * @throws ClientException
     * @throws FactoryError
     * @throws GetError
     * @throws JsonException
     * @throws ReadError
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    #[CheckPermission([Permission::READ])]
    public function get(
        RequestService $requestService,
        SettingRepository $settingRepository,
        MediaStore $mediaStore,
        int $start = 0,
        int $limit = 100,
        array $sort = [],
    ): AjaxResponse {
        $settingModels = $settingRepository->getAllByModuleName(
            $requestService->getModuleName(),
            $this->sessionService->getUserId(),
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
            'data' => iterator_to_array($mediaStore->getList()),
            'total' => $mediaStore->getCount(),
            'settings' => $settings,
            'size' => $mediaStore->getSize(),
        ]);
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws JsonException
     * @throws SaveError
     * @throws ReflectionException
     */
    #[CheckExplorerPermission([Permission::WRITE, Permission::MANAGE])]
    public function postConvert(
        MediaService $mediaService,
        string $dir,
        array $files = [],
        ?string $audioStream = null,
        ?string $subtitleStream = null,
    ): AjaxResponse {
        $userId = $this->sessionService->getUserId();

        return $this->returnSuccess(
            $mediaService->scheduleConvert($userId, $dir, $files, $audioStream, $subtitleStream),
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
     * @throws MediaException
     */
    #[CheckPermission([Permission::READ])]
    public function getConvertStatus(
        MediaService $mediaService,
        #[GetModel(['token' => 'token'])]
        Media $media,
    ): AjaxResponse {
        return $this->returnSuccess($mediaService->getConvertStatus($media));
    }

    #[CheckPermission([Permission::READ])]
    public function getStream(
        MediaService $mediaService,
        #[GetModel(['token' => 'token'])]
        Media $media,
    ): FileResponse {
        return (new FileResponse($this->requestService, $mediaService->getFilename($media)))
            ->setType($media->getType() === TypeService::TYPE_CATEGORY_VIDEO ? 'video/mp4' : 'audio/mp3')
            ->setDisposition(null)
        ;
    }

    /**
     * @param int[] $userIds
     *
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     */
    #[CheckPermission([Permission::WRITE])]
    public function postPosition(
        MediaService $mediaService,
        #[GetModel(['token' => 'token'])]
        Media $media,
        int $position,
        array $userIds,
    ): AjaxResponse {
        foreach (array_unique($userIds) as $userId) {
            $mediaService->savePosition(
                $media,
                $position,
                $userId,
            );
        }

        return $this->returnSuccess();
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     */
    #[CheckPermission([Permission::READ])]
    public function getPosition(
        PositionRepository $positionRepository,
        #[GetModel(['token' => 'token'])]
        Media $media,
        User $permissionUser,
    ): AjaxResponse {
        return $this->returnSuccess(
            $positionRepository->getByMediaAndUserId($media->getId() ?? 0, $permissionUser->getId() ?? 0),
        );
    }

    /**
     * @throws ClientException
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws JsonException
     * @throws MediaException
     * @throws NoAudioError
     * @throws OpenError
     * @throws ProcessError
     * @throws ReadError
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     * @throws SetError
     */
    #[CheckPermission([Permission::READ])]
    public function getToSeeList(ToSeeStore $toSeeStore, ?array $userIds): AjaxResponse
    {
        $toSeeStore->setUserIds(array_values(array_unique($userIds ?: [$this->sessionService->getUserId()])));

        return $this->returnSuccess($toSeeStore->getList(), $toSeeStore->getCount());
    }

    #[CheckPermission([Permission::READ])]
    public function getGet(#[GetModel(['token' => 'token'])] Media $media): AjaxResponse
    {
        return $this->returnSuccess($media);
    }

    /**
     * @throws ExecuteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ReadError
     * @throws CreateError
     * @throws LoadError
     * @throws WriteError
     * @throws FactoryError
     * @throws Exception
     */
    #[CheckDeviceRequestValuePermission([Permission::READ])]
    public function getImage(
        GibsonStoreService $gibsonStoreService,
        ImageService $imageService,
        TypeFactory $typeFactory,
        #[GetModel(['token' => 'token'])]
        Media $media,
        ?int $width = null,
        ?int $height = null,
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
            HttpStatusCode::OK,
            [
                'Pragma' => 'public',
                'Expires' => 0,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => ['must-revalidate, post-check=0, pre-check=0', 'private'],
                'Content-Type' => 'image/jpg',
                'Content-Length' => strlen($body),
                'Content-Transfer-Encoding' => 'binary',
                'Content-Disposition' => 'inline; filename*=UTF-8\'\'image.jpg filename="image.jpg"',
            ],
        );
    }

    /**
     * @throws SelectError
     * @throws DeleteError
     * @throws JsonException
     */
    #[CheckPermission([Permission::DELETE])]
    public function delete(
        MediaRepository $mediaRepository,
        ModelManager $modelManager,
        array $tokens,
    ): AjaxResponse {
        foreach ($mediaRepository->getByTokens($tokens) as $media) {
            $modelManager->delete($media);
        }

        return $this->returnSuccess();
    }

    /**
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     * @throws ClientException
     */
    #[CheckPermission([Permission::READ])]
    public function getConnectedUsers(
        ConnectedUserStore $connectedUserStore,
        ModelWrapper $modelWrapper,
    ): AjaxResponse {
        $connectedUserStore->setUser($this->sessionService->getUser() ?? new User($modelWrapper));

        return $this->returnSuccess($connectedUserStore->getList(), $connectedUserStore->getCount());
    }

    /**
     * @throws FormException
     */
    #[CheckPermission([Permission::WRITE])]
    public function getConnectedUserForm(ConnectedUserForm $connectedUserForm): AjaxResponse
    {
        $modelFormConfig = new ModelFormConfig();

        return $this->returnSuccess($connectedUserForm->getForm($modelFormConfig));
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     * @throws RecordException
     */
    #[CheckPermission([Permission::WRITE])]
    public function postConnectedUser(
        ModelManager $modelManager,
        #[GetMappedModel]
        ConnectedUser $connectedUser,
    ): AjaxResponse {
        $connectedUser->setUserId($this->sessionService->getUserId());

        if ($connectedUser->getConnectedUserId() === $connectedUser->getUserId()) {
            return $this->returnFailure('Same users!');
        }

        $modelManager->saveWithoutChildren($connectedUser);

        return $this->returnSuccess();
    }

    /**
     * @param ConnectedUser[] $connectedUsers
     *
     * @throws DeleteError
     * @throws JsonException
     */
    #[CheckPermission([Permission::DELETE])]
    public function deleteConnectedUsers(
        ModelManager $modelManager,
        #[GetModels(ConnectedUser::class)]
        array $connectedUsers,
    ): AjaxResponse {
        $userId = $this->sessionService->getUserId();

        foreach ($connectedUsers as $connectedUser) {
            if (
                $userId !== $connectedUser->getUserId()
                && $userId !== $connectedUser->getConnectedUserId()
            ) {
                continue;
            }

            $modelManager->delete($connectedUser);
        }

        return $this->returnSuccess();
    }

    /**
     * @throws SaveError
     * @throws MiddlewareException
     * @throws WebException
     * @throws JsonException
     */
    #[CheckPermission([Permission::WRITE])]
    public function postSession(MiddlewareService $middlewareService, string $id): AjaxResponse
    {
        $middlewareService->send('chromecast', 'session', ['id' => $id]);

        return $this->returnSuccess();
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws MiddlewareException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SaveError
     * @throws SelectError
     * @throws WebException
     */
    #[CheckPermission([Permission::READ])]
    public function getChromecastReceiverAppId(
        ModuleRepository $moduleRepository,
        ModelManager $modelManager,
        ModelWrapper $modelWrapper,
        MiddlewareService $middlewareService,
        #[GetSetting('chromecastReceiverAppId', 'core')]
        ?Setting $chromecastReceiverAppId = null,
    ): AjaxResponse {
        if ($chromecastReceiverAppId instanceof Setting) {
            return $this->returnSuccess($chromecastReceiverAppId->getValue());
        }

        $response = $middlewareService->send('chromecast', 'receiverAppId');
        $chromecastReceiverAppId = (new Setting($modelWrapper))
            ->setModule($moduleRepository->getByName('core'))
            ->setKey('chromecastReceiverAppId')
            ->setValue(JsonUtility::decode($response->getBody()->getContent())['data'])
        ;
        $modelManager->saveWithoutChildren($chromecastReceiverAppId);

        return $this->returnSuccess($chromecastReceiverAppId->getValue());
    }
}
