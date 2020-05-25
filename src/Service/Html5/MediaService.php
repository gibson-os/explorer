<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\Html5;

use DateTime;
use GibsonOS\Core\Dto\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\DeleteError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Service\AbstractService;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\Ffmpeg\MediaService as CoreMediaService;
use GibsonOS\Core\Service\File\TypeService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Model\Html5\Media\Position as PositionModel;
use GibsonOS\Module\Explorer\Repository\Html5\MediaRepository;
use OutOfRangeException;

class MediaService extends AbstractService
{
    /**
     * @var CoreMediaService
     */
    private $mediaService;

    /**
     * @var DirService
     */
    private $dirService;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * @var TypeService
     */
    private $typeService;

    /**
     * Media constructor.
     */
    public function __construct(
        CoreMediaService $mediaService,
        DirService $dirService,
        FileService $fileService,
        MediaRepository $mediaRepository,
        TypeService $typeService
    ) {
        $this->mediaService = $mediaService;
        $this->fileService = $fileService;
        $this->dirService = $dirService;
        $this->mediaRepository = $mediaRepository;
        $this->typeService = $typeService;
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ProcessError
     */
    public function convertToMp4(Media $media, string $filename)
    {
        $mediaDto = $this->mediaService->getMedia(
            $this->dirService->addEndSlash($media->getDir()) . $media->getFilename()
        );

        if (!empty($media->getAudioStream())) {
            $mediaDto->selectAudioStream($media->getAudioStream() ?? '');
        }

        if (!empty($media->getSubtitleStream())) {
            $mediaDto->selectSubtitleStream(
                $media->getSubtitleStream() === Media::SUBTITLE_NONE
                    ? null
                    : $media->getSubtitleStream()
            );
        }

        $this->mediaService->convert(
            $mediaDto,
            $filename,
            'libx264',
            'libfdk_aac',
            [
                'crf' => 23,
                'preset' => 'veryfast',
                'movflags' => 'faststart',
            ]
        );
    }

    /**
     * @throws DeleteError
     * @throws FileNotFound
     * @throws GetError
     * @throws ProcessError
     */
    public function convertToWebm(Media $media, string $filename)
    {
        $mediaDto = $this->mediaService->getMedia(
            $this->dirService->addEndSlash($media->getDir()) . $media->getFilename()
        );

        if (!empty($media->getAudioStream())) {
            $mediaDto->selectAudioStream($media->getAudioStream());
        }

        if (!empty($media->getSubtitleStream())) {
            $mediaDto->selectSubtitleStream(
                $media->getSubtitleStream() === Media::SUBTITLE_NONE
                    ? null
                    : $media->getSubtitleStream()
            );
        }

        $this->mediaService->convert(
            $mediaDto,
            $filename,
            'libvpx',
            'libvorbis',
            [
                'b:v' => '1500k',
                'q:a' => 4,
            ]
        );
    }

    /**
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws FileNotFound
     * @throws OpenError
     * @throws ProcessError
     * @throws SetError
     */
    public function getConvertStatus(Media $media): ConvertStatus
    {
        if ($media->getStatus() !== 'generate') {
            return new ConvertStatus($media->getStatus());
        }

        $mediaDto = $this->mediaService->getMedia(
            $this->dirService->addEndSlash($media->getDir()) . $media->getFilename()
        );

        return $this->mediaService->getConvertStatus($mediaDto, $media->getToken() . '.mp4')
            ->setStatus($media->getStatus())
        ;
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SaveError
     */
    public function savePosition(Media $media, int $currentPosition, int $userId): void
    {
        if ($currentPosition === 0) {
            throw new OutOfRangeException('Position 0 ist nicht gültig!');
        }

        (new PositionModel())
            ->setMediaId($media->getId() ?? 0)
            ->setPosition($currentPosition)
            ->setUserId($userId)
            ->setModified(new DateTime())
            ->save()
        ;
    }

    /**
     * @throws DateTimeError
     * @throws GetError
     * @throws SaveError
     */
    public function scheduleConvert(
        int $userId,
        string $dir,
        array $files = [],
        string $audioStream = null,
        string $subtitleStream = null
    ): void {
        if (empty($files)) {
            $files = $this->dirService->getFiles($dir);
        }

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->scheduleConvert(
                    $userId,
                    $dir,
                    array_map(function ($path) {
                        return $this->fileService->getFilename($path);
                    }, $this->dirService->getFiles($file)),
                    $audioStream,
                    $subtitleStream
                );

                continue;
            }

            $category = $this->typeService->getCategory($file);

            if ($category !== TypeService::TYPE_CATEGORY_VIDEO) {
                continue;
            }

            (new Media())
                ->setToken($this->mediaRepository->getFreeToken())
                ->setDir($dir)
                ->setFilename($file)
                ->setAudioStream($audioStream)
                ->setSubtitleStream($subtitleStream)
                ->setType($category)
                ->setUserId($userId)
                ->save()
            ;
        }
    }
}
