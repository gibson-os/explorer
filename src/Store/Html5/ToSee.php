<?php
namespace GibsonOS\Module\Explorer\Store\Html5;

use GibsonOS\Core\Exception\Ffmpeg\ConvertStatusError;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Factory\Media as MediaFactory;
use GibsonOS\Core\Store\AbstractDatabaseStore;
use GibsonOS\Core\Utility\Dir;
use GibsonOS\Module\Explorer\Model\Html5\Media\Position;
use GibsonOS\Module\Explorer\Service\GibsonStore;
use GibsonOS\Module\Explorer\Service\Html5\Media as MediaService;
use mysqlDatabase;
use mysqlTable;

class ToSee extends AbstractDatabaseStore
{
    public function __construct(mysqlDatabase $database = null)
    {
        parent::__construct($database);

        $this->where[] = '`' . $this->getTableName() . '`.`status` IN(' .
            $this->database->escape(MediaService::STATUS_GENERATE) . ', ' .
            $this->database->escape(MediaService::STATUS_GENERATED) . ')';
    }

    /**
     * @return array[]
     * @throws ExecuteError
     * @throws ReadError
     * @throws ConvertStatusError
     * @throws FileNotFound
     */
    public function getList()
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
        $this->table->setOrderBy( '`orderDate` DESC');
        $this->table->selectUnion(false);

        $gibsonStore = GibsonStore::getInstance();
        $medias = [];

        foreach ($this->table->connection->fetchObjectList() as $media) {
            $filenamePattern = $this->generateFilenamePattern($media->filename);
            $key = Dir::escapeForGlob($media->dir) . $filenamePattern;

            $media->position = (int)$media->position;
            $media->duration = (int)$gibsonStore->getFileMeta(
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
                'nextFiles' => count($nextFiles)
            ];

            if ($media->status === MediaService::STATUS_GENERATE) {
                $mediaService = MediaFactory::create($media->dir . $media->filename);
                $convertStatus = $mediaService->getConvertStatus($media->token . '.mp4');

                $listMedia['convertPercent'] = $convertStatus->getPercent();
                $listMedia['convertTime'] = $convertStatus->getTime()->getTimestamp();
                $listMedia['convertTimeRemaining'] = $convertStatus->getTimeRemaining()->getTimestamp();
            }

            $list[] = $listMedia;
        }

        return $list;
    }

    private function generateFilenamePattern($filename)
    {
        return preg_replace('/(s?)\d{1,3}e?\d.*/i', '$1*', Dir::escapeForGlob($filename));
    }

    /**
     * @param $media
     * @param $pattern
     * @return array
     */
    private function getNextFiles($media, $pattern)
    {
        $fileNames = glob($pattern);
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

    /**
     * @return string
     */
    protected function getTableName()
    {
        return 'explorer_html5_media';
    }

    /**
     * @return string
     */
    protected function getCountField()
    {
        return '`id`';
    }

    /**
     * @return string[]
     */
    protected function getOrderMapping()
    {
        return [];
    }
}