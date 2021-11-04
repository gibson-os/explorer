<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store\Html5;

use GibsonOS\Core\Dto\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Exception\CreateError;
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
use GibsonOS\Module\Explorer\Model\Html5\Media;
use GibsonOS\Module\Explorer\Model\Html5\Media\Position;
use GibsonOS\Module\Explorer\Service\GibsonStoreService;
use GibsonOS\Module\Explorer\Service\Html5\MediaService as MediaService;
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
     * @throws CreateError
     */
    public function __construct(
        mysqlDatabase $database,
        private DirService $dir,
        private MediaService $media,
        private GibsonStoreService $gibsonStore
    ) {
        parent::__construct($database);
    }

    protected function setWheres(): void
    {
        $tableName = $this->getTableName();
        $this->addWhere(
            '`' . $tableName . '`.`status` IN (?, ?)',
            [ConvertStatus::STATUS_GENERATE, ConvertStatus::STATUS_GENERATED]
        );

        if (count($this->userIds) > 0) {
            $this->addWhere(
                '`' . $tableName . '`.`user_id` IN (' . $this->table->getParametersString($this->userIds) . ')',
                $this->userIds
            );
        }
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

        $this->table->appendJoinLeft(Position::getTableName(), '`' . $this->getTableName() . '`.`id`=`explorer_html5_media_position`.`media_id`');
    }

    /**
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws FileNotFound
     * @throws OpenError
     * @throws ProcessError
     * @throws ReadError
     * @throws NoAudioError
     * @throws SetError
     */
    public function getList(): iterable
    {
        $select = [
            'token' => 'token',
            'dir' => 'dir',
            'filename' => 'filename',
            'status' => 'status',
            'position' => 'position',
        ];

        $this->initTable();
        $this->table->setSelectString(array_merge($select, ['orderDate' => 'added` AS `orderDate']));

        $tableName = $this->getTableName();
        $positionTable = new mysqlTable($this->database, Position::getTableName());
        $positionTable->setSelectString(array_merge($select, ['orderDate' => 'modified` AS `orderDate']));
        $positionTable->appendJoin(
            $tableName,
            '`' . $tableName . '`.`id`=`explorer_html5_media_position`.`media_id`'
        );

        $this->table
            ->appendUnion()
            ->appendUnion($positionTable->getSelect())
            ->setOrderBy('`orderDate` DESC')
            ->selectUnion(false)
        ;

        $medias = [];

        foreach ($this->table->connection->fetchObjectList() as $media) {
            $filenamePattern = $this->generateFilenamePattern($media->filename);
            $key = $this->dir->escapeForGlob($media->dir) . $filenamePattern;

            $media->position = (int) $media->position;

            try {
                $media->duration = (int) $this->gibsonStore->getFileMeta(
                    $media->dir . $media->filename,
                    'duration',
                    0
                );
            } catch (ExecuteError) {
                $media->dudation = 0;
            }

            if (isset($medias[$key])) {
                $oldMedia = $medias[$key];

                if (
                    // Wenn es eine nicht zuende geguckte folge gibt soll die bevorzugt werden
                    // und zwar die die letzte
                    /*(
                        $media->position === 0 &&
                        $oldMedia->position !== 0
                    ) || (*/
                        $oldMedia->position >= ($oldMedia->duration - ($oldMedia->duration / 20)) &&
                        strcmp($oldMedia->filename, $media->filename) < 0 // Alter Dateiname ist kleiner als der neue
                    //)
                ) {
                    $medias[$key] = $media;
                }
            } else {
                $medias[$key] = $media;
            }
        }

        foreach ($medias as $pattern => $media) {
            $nextFiles = $this->getNextFiles($media, $pattern);

            if (
                $media->position >= ($media->duration - ($media->duration / 20)) &&
                count($nextFiles) === 0
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
                $convertStatus = $this->media->getConvertStatus($mediaModel);

                $listMedia['convertPercent'] = $convertStatus->getPercent();
                $listMedia['convertTime'] = $convertStatus->getTime()->getTimestamp();
                $timeRemaining = $convertStatus->getTimeRemaining();
                $listMedia['convertTimeRemaining'] =
                    $timeRemaining === null
                    ? 0
                    : $timeRemaining->getTimestamp()
                ;
            }

            yield $listMedia;
        }
    }

    private function generateFilenamePattern($filename): string
    {
        return preg_replace('/(s?)\d{1,3}e?\d.*/i', '$1*', $this->dir->escapeForGlob($filename));
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
}
