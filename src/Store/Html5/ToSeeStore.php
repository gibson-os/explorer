<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store\Html5;

use GibsonOS\Core\Attribute\GetTableName;
use GibsonOS\Core\Dto\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\Ffmpeg\NoAudioError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\File\TypeService;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Module\Explorer\Exception\MediaException;
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Model\Html5\Media\Position;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\Html5\MediaService;
use mysqlDatabase;
use mysqlTable;
use stdClass;

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
        mysqlDatabase $database,
        private readonly DirService $dirService,
        private readonly MediaService $mediaService,
        private readonly GibsonStoreService $gibsonStoreService,
        #[GetTableName(Position::class)] private readonly string $positionTableName
    ) {
        parent::__construct($database);
    }

    protected function setWheres(): void
    {
        $this->addWhere('`' . $this->tableName . '`.`status` IN (?, ?)');
    }

    /**
     * @param int[] $userIds
     */
    public function setUserIds(array $userIds): ToSeeStore
    {
        $this->userIds = $userIds;

        return $this;
    }

    protected function initTable(): void
    {
        parent::initTable();

        $userIds = $this->userIds ?: [0];
        $this->table->appendJoinLeft(
            $this->positionTableName,
            '`' . $this->tableName . '`.`id`=`' . $this->positionTableName . '`.`media_id` AND ' .
            '`' . $this->positionTableName . '`.`user_id` IN (' . $this->table->getParametersString($userIds) . ')',
        );

        foreach ($userIds as $userId) {
            $this->table->addWhereParameter($userId);
        }

        $this->table->addWhereParameter(ConvertStatus::STATUS_GENERATE);
        $this->table->addWhereParameter(ConvertStatus::STATUS_GENERATED);
    }

    /**
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws OpenError
     * @throws ProcessError
     * @throws ReadError
     * @throws NoAudioError
     * @throws SetError
     * @throws MediaException
     */
    public function getList(): iterable
    {
        $this->seenMedias = [];

        foreach ($this->getMedias() as $pattern => $media) {
            $nextFiles = $this->getNextFiles($media, $pattern);

            if (
                $this->hasSeen($media)
                && count($nextFiles) === 0
            ) {
                continue;
            }

            $listMedia = [
                'html5VideoToken' => $media->token,
                'html5MediaToken' => $media->token,
                'dir' => $media->dir,
                'filename' => $media->filename,
                'status' => $media->status,
                'duration' => $media->duration,
                'position' => $media->position,
                'nextFiles' => count($nextFiles),
                'category' => TypeService::TYPE_CATEGORY_VIDEO,
            ];

            if ($media->status === ConvertStatus::STATUS_GENERATE) {
                $mediaModel = (new Media())
                    ->setFilename($media->token . '.mp4')
                    ->setStatus($media->status)
                    ->setDir($media->dir)
                    ->setFilename($media->filename)
                    ->setToken($media->token)
                ;

                try {
                    $convertStatus = $this->mediaService->getConvertStatus($mediaModel);
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

    private function getNextFiles(stdClass $media, string $pattern): array
    {
        $fileNames = (array) glob($pattern);
        $mediaFilePath = $media->dir . $media->filename;
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

    private function hasSeen(object $media): bool
    {
        return $this->seenMedias[$media->dir . $media->filename] ?? false;
    }

    private function setTableForList(): void
    {
        $select = [
            'token' => 'token',
            'dir' => 'dir',
            'filename' => 'filename',
            'status' => 'status',
            'position' => 'position',
        ];

        $this->initTable();
        $this->table->setSelectString('`' . implode('`, `', $select) . '`, `added` AS `orderDate`, 0 AS `isPosition`');

        $tableName = $this->tableName;
        $positionTable = new mysqlTable($this->database, $this->positionTableName);
        $positionTable->setSelectString('`' . implode('`, `', $select) . '`, `modified` AS `orderDate`, 1 AS `isPosition`');
        $positionTable->appendJoin(
            $tableName,
            '`' . $tableName . '`.`id`=`' . $this->positionTableName . '`.`media_id`'
        );
        $userIds = $this->userIds ?: [0];
        $positionTable->setWhere(
            '`' . $this->positionTableName . '`.`user_id` IN ' .
            '(' . $positionTable->getParametersString($userIds) . ')'
        );

        foreach ($userIds as $userId) {
            $this->table->addWhereParameter($userId);
        }

        $this->table
            ->appendUnion($positionTable->getSelect())
            ->setOrderBy('`isPosition` DESC, `orderDate` DESC')
            ->selectPrepared(false)
        ;
    }

    /**
     * @throws ReadError
     */
    private function getMedias(): array
    {
        $this->setTableForList();
        $medias = [];

        foreach ($this->table->connection->fetchObjectList() as $media) {
            $filenamePattern = $this->generateFilenamePattern($media->filename);
            $key = $this->dirService->escapeForGlob($media->dir) . $filenamePattern;
            $media->position = (int) $media->position;

            try {
                $media->duration = (int) $this->gibsonStoreService->getFileMeta(
                    $media->dir . $media->filename,
                    'duration',
                    0
                );
            } catch (ExecuteError) {
                $media->duration = 0;
            }

            if ($media->position >= ($media->duration - ($media->duration / 10))) {
                $this->seenMedias[$media->dir . $media->filename] = true;
            }

            if (isset($medias[$key])) {
                $oldMedia = $medias[$key];

                if (
                    !$this->hasSeen($media)
                    && (strcmp($oldMedia->filename, $media->filename) > 0 || $this->hasSeen($oldMedia))
                ) {
                    $medias[$key] = $media;
                }

                if ($oldMedia->filename === $media->filename && $media->position > $oldMedia->position) {
                    $medias[$key] = $media;
                }
            } else {
                $medias[$key] = $media;
            }
        }

        return $medias;
    }
}
