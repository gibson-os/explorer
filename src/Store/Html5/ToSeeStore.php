<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Store\Html5;

use GibsonOS\Core\Dto\Ffmpeg\ConvertStatus;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\DateTimeError;
use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\File\OpenError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\ProcessError;
use GibsonOS\Core\Exception\SetError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Service\DirService;
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
     * @var DirService
     */
    private $dir;

    /**
     * @var MediaService
     */
    private $media;

    /**
     * @var GibsonStoreService
     */
    private $gibsonStore;

    /**
     * @throws CreateError
     */
    public function __construct(
        mysqlDatabase $database,
        DirService $dir,
        MediaService $media,
        GibsonStoreService $gibsonStore
    ) {
        parent::__construct($database);

        $this->where[] = '`' . $this->getTableName() . '`.`status` IN(' .
            $this->database->escape(ConvertStatus::STATUS_GENERATE) . ', ' .
            $this->database->escape(ConvertStatus::STATUS_GENERATED) . ')';

        $this->dir = $dir;
        $this->media = $media;
        $this->gibsonStore = $gibsonStore;
    }

    /**
     * @throws ConvertStatusError
     * @throws DateTimeError
     * @throws ExecuteError
     * @throws FileNotFound
     * @throws GetError
     * @throws OpenError
     * @throws ProcessError
     * @throws ReadError
     * @throws SetError
     *
     * @return array[]
     */
    public function getList(): array
    {
        $select = [
            'token' => 'token',
            'dir' => 'dir',
            'filename' => 'filename',
            'status' => 'status',
            'position' => 'position',
        ];

        $this->table->setSelectString(array_merge($select, ['orderDate' => 'added` AS `orderDate']));
        $this->table->appendJoinLeft(
            Position::getTableName(),
            '`' . $this->getTableName() . '`.`id`=`explorer_html5_media_position`.`media_id` AND ' .
            '`explorer_html5_media_position`.`user_id`=1' // @todo user ermitteln
        );
        $this->table->setWhere($this->getWhere());

        $positionTable = new mysqlTable($this->database, Position::getTableName());
        $positionTable->setSelectString(array_merge($select, ['orderDate' => 'modified` AS `orderDate']));
        $positionTable->appendJoin(
            $this->getTableName(),
            '`' . $this->getTableName() . '`.`id`=`explorer_html5_media_position`.`media_id`'
        );
        $positionTable->setWhere('`explorer_html5_media_position`.`user_id`=1');  // @todo user ermitteln

        $this->table->appendUnion();
        $this->table->appendUnion($positionTable->getSelect());
        $this->table->setOrderBy('`orderDate` DESC');
        $this->table->selectUnion(false);

        $medias = [];

        foreach ($this->table->connection->fetchObjectList() as $media) {
            $filenamePattern = $this->generateFilenamePattern($media->filename);
            $key = $this->dir->escapeForGlob($media->dir) . $filenamePattern;

            $media->position = (int) $media->position;
            $media->duration = (int) $this->gibsonStore->getFileMeta(
                $media->dir . $media->filename,
                'duration',
                0
            );

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

        $list = [];

        foreach ($medias as $pattern => $media) {
            $nextFiles = $this->getNextFiles($media, $pattern);

            if (
                $media->position >= ($media->duration - ($media->duration / 20)) &&
                count($nextFiles) === 0
            ) {
                continue;
            }

            $listMedia = [
                'token' => $media->token,
                'dir' => $media->dir,
                'filename' => $media->filename,
                'status' => $media->status,
                'duration' => $media->duration,
                'position' => $media->position,
                'nextFiles' => count($nextFiles),
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
                $listMedia['convertTimeRemaining'] =
                    $convertStatus->getTimeRemaining() === null
                    ? 0
                    : $convertStatus->getTimeRemaining()->getTimestamp()
                ;
            }

            $list[] = $listMedia;
        }

        return $list;
    }

    private function generateFilenamePattern($filename)
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

    protected function getTableName(): string
    {
        return 'explorer_html5_media';
    }

    protected function getCountField(): string
    {
        return '`id`';
    }

    /**
     * @return string[]
     */
    protected function getOrderMapping(): array
    {
        return [];
    }
}
