<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Controller;

use DateTime;
use GibsonOS\Core\Attribute\GetModel;
use GibsonOS\Core\Controller\AbstractController;
use GibsonOS\Core\Enum\HttpStatusCode;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\CreateError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Service\File\TypeService;
use GibsonOS\Core\Service\ImageService;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\FileResponse;
use GibsonOS\Core\Service\Response\Response;
use GibsonOS\Core\Service\Response\ResponseInterface;
use GibsonOS\Module\Explorer\Attribute\CheckChromecastPermission;
use GibsonOS\Module\Explorer\Exception\MediaException;
use GibsonOS\Module\Explorer\Factory\File\TypeFactory;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Repository\Html5\Media\PositionRepository;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use GibsonOS\Module\Explorer\Store\Html5\ToSeeStore;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class MiddlewareController extends AbstractController
{
    /**
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws JsonException
     * @throws MediaException
     * @throws NoAudioError
     * @throws OpenError
     * @throws ProcessError
     * @throws ReadError
     * @throws ReflectionException
     * @throws SelectError
     * @throws SetError
     * @throws ClientException
     * @throws RecordException
     */
    #[CheckChromecastPermission([Permission::READ])]
    public function getToSeeList(
        ToSeeStore $toSeeStore,
        array $userIds,
    ): AjaxResponse {
        $toSeeStore->setUserIds($userIds);

        return $this->returnSuccess($toSeeStore->getList(), $toSeeStore->getCount());
    }

    #[CheckChromecastPermission([Permission::READ])]
    public function headStream(
        MediaService $mediaService,
        #[GetModel(['token' => 'token'])]
        Media $media,
    ): Response {
        $fileResponse = $this->getStream($mediaService, $media);

        return new Response('', $fileResponse->getCode(), $fileResponse->getHeaders());
    }

    #[CheckChromecastPermission([Permission::READ])]
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
     * @throws SaveError
     * @throws JsonException
     * @throws ReflectionException
     */
    #[CheckChromecastPermission([Permission::WRITE])]
    public function postPosition(
        MediaService $mediaService,
        #[GetModel(['token' => 'token'])]
        Media $media,
        int $position,
        array $userIds,
    ): AjaxResponse {
        foreach ($userIds as $userId) {
            $mediaService->savePosition(
                $media,
                $position,
                $userId,
            );
        }

        return $this->returnSuccess();
    }

    /**
     * @throws ExecuteError
     * @throws FileNotFound
     * @throws ReadError
     * @throws FactoryError
     * @throws GetError
     * @throws CreateError
     * @throws LoadError
     * @throws WriteError
     */
    #[CheckChromecastPermission([Permission::READ])]
    public function getImage(
        GibsonStoreService $gibsonStoreService,
        ImageService $imageService,
        TypeFactory $typeFactory,
        #[GetModel(['token' => 'token'])]
        Media $media,
        ?int $width = null,
        ?int $height = null,
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
     * @throws DateTimeError
     * @throws ExecuteError
     * @throws FileNotFound
     * @throws MediaException
     * @throws NoAudioError
     * @throws OpenError
     * @throws ProcessError
     * @throws ReadError
     * @throws SelectError
     * @throws SetError
     */
    #[CheckChromecastPermission([Permission::READ])]
    public function get(
        GibsonStoreService $gibsonStoreService,
        PositionRepository $positionRepository,
        MediaService $mediaService,
        #[GetModel(['token' => 'token'])]
        Media $media,
        array $userIds,
    ): AjaxResponse {
        $mediaData = $media->jsonSerialize();
        $mediaData['duration'] = (int) $gibsonStoreService->getFileMeta(
            $media->getDir() . $media->getFilename(),
            'duration',
            0,
        );
        $positions = [];

        foreach ($userIds as $userId) {
            try {
                $positions[] = $positionRepository->getByMediaAndUserId($media->getId() ?? 0, $userId)->getPosition();
            } catch (SelectError) {
                // Do nothing
            }

            foreach ($positionRepository->getByMediaAndConnectedUserId($media->getId() ?? 0, $userId) as $position) {
                $positions[] = $position->getPosition();
            }
        }

        $mediaData['position'] = $positions !== [] ? max($positions) : 0;

        try {
            $convertStatus = $mediaService->getConvertStatus($media);
        } catch (ConvertStatusError) {
            $convertStatus = null;
        }

        $mediaData['convertPercent'] = $convertStatus?->getPercent() ?? 0;
        $mediaData['convertTime'] = $convertStatus?->getTime()?->getTimestamp() ?? 0;
        $timeRemaining = $convertStatus?->getTimeRemaining();
        $mediaData['convertTimeRemaining'] = $timeRemaining instanceof DateTime ? $timeRemaining->getTimestamp() : 0;

        return $this->returnSuccess($mediaData);
    }
}
