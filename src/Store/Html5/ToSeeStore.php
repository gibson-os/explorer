<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store\Html5;

use Generator;
use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Enum\Ffmpeg\ConvertStatus as ConvertStatusEnum;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\File\TypeService;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Wrapper\DatabaseStoreWrapper;
use GibsonOS\Module\Explorer\Exception\MediaException;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Model\Html5\Media\Position;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use JsonException;
use MDO\Dto\Query\Join;
use MDO\Dto\Select;
use MDO\Enum\JoinType;
use MDO\Enum\OrderDirection;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class ToSeeStore extends AbstractDatabaseStore
{
    /**
     * @var int[]
     */
    private array $userIds = [];

    /**
     * @var array<string, bool>
     */
    private array $seenMedias = [];

    public function __construct(
        DatabaseStoreWrapper $databaseStoreWrapper,
        private readonly DirService $dirService,
        private readonly MediaService $mediaService,
        private readonly GibsonStoreService $gibsonStoreService,
        #[GetTableName(Position::class)]
        private readonly string $positionTableName,
    ) {
        parent::__construct($databaseStoreWrapper);
    }

    protected function setWheres(): void
    {
        $this
            ->addWhere(
                '`m`.`status` IN (:statusGenerate, :statusGenerated)',
                ['statusGenerate' => ConvertStatusEnum::GENERATE->name, 'statusGenerated' => ConvertStatusEnum::GENERATED->name],
            )
        ;
    }

    /**
     * @param int[] $userIds
     */
    public function setUserIds(array $userIds): ToSeeStore
    {
        $this->userIds = $userIds;

        return $this;
    }

    protected function initQuery(): void
    {
        parent::initQuery();
        $userIds = $this->userIds ?: [0];

        $this->selectQuery
            ->addJoin(new Join(
                $this->getTable($this->positionTableName),
                'mp',
                sprintf(
                    '`m`.`id`=`mp`.`media_id` AND `mp`.`user_id` IN (%s)',
                    $this->getDatabaseStoreWrapper()->getSelectService()->getParametersString($userIds),
                ),
                JoinType::LEFT,
            ))
            ->addParameters($userIds)
            ->setSelects(
                $this->getDatabaseStoreWrapper()->getSelectService()->getSelects([
                    new Select($this->table, 'm', 'media_'),
                    new Select($this->getTable($this->positionTableName), 'mp', 'position_'),
                ]),
            )
            ->setOrders([
                'IF(`mp`.`media_id` IS NULL, 0, 1)' => OrderDirection::DESC,
                'IFNULL(`mp`.`modified`, `m`.`added`)' => OrderDirection::DESC,
            ])
        ;
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
    public function getList(): iterable
    {
        $this->seenMedias = [];

        foreach ($this->getMedias() as $pattern => $models) {
            $media = $models['media'];
            $position = $models['position'];
            $nextFiles = $this->getNextFiles($media, $pattern);

            if (
                $this->hasSeen($media)
                && count($nextFiles) === 0
            ) {
                continue;
            }

            $listMedia = [
                'html5VideoToken' => $media->getToken(),
                'html5MediaToken' => $media->getToken(),
                'dir' => $media->getDir(),
                'filename' => $media->getFilename(),
                'status' => $media->getStatus(),
                'duration' => $models['duration'],
                'position' => $position?->getPosition() ?? 0,
                'nextFiles' => count($nextFiles),
                'category' => TypeService::TYPE_CATEGORY_VIDEO,
            ];

            if ($media->getStatus() === ConvertStatusEnum::GENERATE) {
                try {
                    $convertStatus = $this->mediaService->getConvertStatus($media);
                } catch (FileNotFound) {
                    $convertStatus = null;
                }

                $listMedia['convertPercent'] = $convertStatus?->getPercent() ?? 0;
                $listMedia['convertTime'] = $convertStatus?->getTime()?->getTimestamp() ?? 0;
                $listMedia['convertTimeRemaining'] = $convertStatus?->getTimeRemaining()?->getTimestamp() ?? 0;
            }

            yield $listMedia;
        }
    }

    private function generateFilenamePattern($filename): string
    {
        return preg_replace('/(s?)\d{1,3}e?\d.*/i', '$1*', $this->dirService->escapeForGlob($filename));
    }

    private function getNextFiles(Media $media, string $pattern): array
    {
        $fileNames = (array) glob($pattern);
        $mediaFilePath = $media->getDir() . $media->getFilename();
        asort($fileNames);

        $add = false;
        $filesWithBiggerNumbers = [];

        foreach ($fileNames as $fileName) {
            if ($add) {
                if (isset($this->seenMedias[$fileName])) {
                    continue;
                }

                $filesWithBiggerNumbers[] = $fileName;
            }

            if ($mediaFilePath === $fileName) {
                $add = true;
            }
        }

        return $filesWithBiggerNumbers;
    }

    protected function getModelClassName(): string
    {
        return Media::class;
    }

    protected function getAlias(): ?string
    {
        return 'm';
    }

    private function hasSeen(Media $media): bool
    {
        return $this->seenMedias[$media->getDir() . $media->getFilename()] ?? false;
    }

    /**
     * @throws ClientException
     * @throws JsonException
     * @throws RecordException
     * @throws ReflectionException
     * @throws SelectError
     *
     * @return Generator<array{media: Media, position: ?Position}>
     */
    protected function getModels(): Generator
    {
        $databaseStoreWrapper = $this->getDatabaseStoreWrapper();
        $result = $databaseStoreWrapper->getClient()->execute($this->selectQuery);

        foreach ($result->iterateRecords() as $record) {
            /** @var Media $media */
            $media = $this->getModel($record, 'media_');
            $position = null;

            if ($record->get('position_media_id')->getValue() !== null) {
                $position = new Position($databaseStoreWrapper->getModelWrapper());
                $databaseStoreWrapper->getModelManager()->loadFromRecord($record, $position, 'position_');
            }

            yield ['media' => $media, 'position' => $position];
        }
    }

    /**
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     * @throws ReflectionException
     * @throws ReadError
     *
     * @return array<string, array{media: Media, position: ?Position, duration: int}>
     */
    private function getMedias(): array
    {
        $medias = [];

        foreach ($this->getModels() as $models) {
            $media = $models['media'];
            $position = $models['position'];
            $filenamePattern = $this->generateFilenamePattern($media->getFilename());
            $key = $this->dirService->escapeForGlob($media->getDir()) . $filenamePattern;

            try {
                $duration = (int) $this->gibsonStoreService->getFileMeta(
                    $media->getDir() . $media->getFilename(),
                    'duration',
                    0,
                );
            } catch (ExecuteError) {
                $duration = 0;
            }

            if ($position !== null && $position->getPosition() >= ($duration - ($duration / 10))) {
                $this->seenMedias[$media->getDir() . $media->getFilename()] = true;
            }

            $mediaArray = $models;
            $mediaArray['duration'] = $duration;

            if (isset($medias[$key])) {
                $oldMedia = $medias[$key]['media'];
                $oldPosition = $medias[$key]['position'];

                if (
                    !$this->hasSeen($media)
                    && (strcmp($oldMedia->getFileName(), $media->getFilename()) > 0 || $this->hasSeen($oldMedia))
                ) {
                    $medias[$key] = $mediaArray;
                }

                if (
                    $oldMedia->getFilename() === $media->getFilename()
                    && ($position?->getPosition() ?? 0) > ($oldPosition?->getPosition() ?? 0)
                ) {
                    $medias[$key] = $mediaArray;
                }
            } else {
                $medias[$key] = $mediaArray;
            }
        }

        return $medias;
    }
}
