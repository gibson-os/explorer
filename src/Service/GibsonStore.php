<?php
namespace GibsonOS\Module\Explorer\Service;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Factory\Image\Manipulate as ManipulateFactory;
use GibsonOS\Core\Factory\Image\Thumbnail;
use GibsonOS\Core\Factory\SqLite as SqLiteFactory;
use GibsonOS\Core\Service\AbstractSingletonService;
use GibsonOS\Core\Service\Image;
use GibsonOS\Core\Service\SqLite;
use GibsonOS\Core\Utility\Dir;
use GibsonOS\Core\Utility\File;
use GibsonOS\Core\Utility\Json;
use SQLite3;

class GibsonStore extends AbstractSingletonService
{
    CONST META_TABLE_NAME = 'meta';
    CONST META_CREATE_QUERY = 'CREATE TABLE meta (`key` varchar(32), `value` text, PRIMARY KEY (`key`))';
    CONST FILE_META_TABLE_NAME = 'fileMeta';
    CONST FILE_META_CREATE_QUERY = 'CREATE TABLE fileMeta (`chksum` varchar(32), `filename` varchar(255), `date` int, `key` varchar(32), `value` text, PRIMARY KEY (`key`, `filename`))';
    CONST IMAGE_TABLE_NAME = 'image';
    CONST IMAGE_CREATE_QUERY = 'CREATE TABLE image (`chksum` varchar(32), `filename` varchar(255), `date` int, `image` blob, PRIMARY KEY (`chksum`))';
    CONST THUMBNAIL_TABLE_NAME = 'thumb';
    CONST THUMBNAIL_CREATE_QUERY = 'CREATE TABLE thumb (`chksum` varchar(32), `filename` varchar(255), `date` int, `image` blob, PRIMARY KEY (`chksum`))';

    /**
     * @var SqLite[]
     */
    private $stores = [];

    /**
     * @return AbstractSingletonService|GibsonStore
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * @param string $dir
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @throws ExecuteError
     * @throws ReadError
     */
    public function getDirMeta(string $dir, string $key, $default = null)
    {
        $store = $this->getStoreToRead($dir);

        if (!$store->hasTable(self::META_TABLE_NAME)) {
            return $default;
        }

        try {
            $query = $store->query(
                "SELECT * FROM " . self::META_TABLE_NAME .
                " WHERE `key`='" . SQLite3::escapeString($key) . "'"
            );
        } catch (ExecuteError $exception) {
            return $default;
        }

        $row = $query->fetchArray(SQLITE3_ASSOC);

        if ($row !== false) {
            return $row['value'];
        }

        return $default;
    }

    /**
     * @param string $dir
     * @param null|array $keys
     * @param mixed $default
     * @return mixed
     * @throws ExecuteError
     * @throws ReadError
     */
    public function getDirMetas(string $dir, array $keys = null, $default = null)
    {
        $store = $this->getStoreToRead($dir);

        if (!$store->hasTable(self::META_TABLE_NAME)) {
            return $default;
        }

        $where = null;

        if (is_array($keys)) {
            foreach ($keys as $arrayKey => $item) {
                $keys[$arrayKey] = SQLite3::escapeString($item);
            }

            $where = " WHERE `key` IN ('" . implode("', '", $keys) . "')";
        }

        try {
            $query = $store->query("SELECT * FROM " . self::META_TABLE_NAME . $where);
        } catch (ExecuteError $exception) {
            return $default;
        }

        $returnList = [];

        while ($row = $query->fetchArray(SQLITE_ASSOC)) {
            $returnList[$row['key']] = $row['value'];
        }

        if (!count($returnList)) {
            return $default;
        }

        return $returnList;
    }

    /**
     * @param string $dir
     * @param string $key
     * @param mixed $value
     * @throws WriteError
     * @throws ExecuteError
     */
    public function setDirMeta(string $dir, string $key, $value)
    {
        $store = $this->getStoreToWrite($dir);
        $store->addTableIfNotExists(self::META_TABLE_NAME, self::META_CREATE_QUERY);
        $store->execute("REPLACE INTO " . self::META_TABLE_NAME . " VALUES('" . SQLite3::escapeString($key) . "', '" . SQLite3::escapeString($value) . "')");
    }

    /**
     * @param string $dir
     * @param array $values
     * @throws ExecuteError
     * @throws WriteError
     */
    public function setDirMetas(string $dir, array $values)
    {
        foreach ($values as $key => $value) {
            $this->setDirMeta($dir, $key, $value);
        }
    }

    /**
     * @param string $path
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @throws ExecuteError
     * @throws ReadError
     */
    public function getFileMeta(string $path, string $key, $default = null)
    {
        $dir = File::getDir($path);
        $filename = File::getFilename($path);

        $store = $this->getStoreToRead($dir);

        if (!$store->hasTable(self::FILE_META_TABLE_NAME)) {
            return $default;
        }

        try {
            $query = $store->query(
                "SELECT * FROM " . self::FILE_META_TABLE_NAME . " WHERE " .
                "`filename`='" . SQLite3::escapeString($filename) . "' AND " .
                "`date`='" . SQLite3::escapeString(filemtime($path)) . "' AND `key`='" . SQLite3::escapeString($key) . "'"
            );
        } catch (ExecuteError $e) {
            return $default;
        }

        $row = $query->fetchArray(SQLITE3_ASSOC);

        if ($row !== false) {
            $value = Json::decode($row['value']);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $value;
            }

            return $row['value'];
        }

        return $default;
    }

    /**
     * @param string $path
     * @param null|array $keys
     * @param mixed $default
     * @return mixed
     * @throws ReadError
     * @throws ExecuteError
     */
    public function getFileMetas(string $path, array $keys = null, $default = null)
    {
        $dir = File::getDir($path);
        $filename = File::getFilename($path);

        $store = $this->getStoreToRead($dir);

        if (!$store->hasTable(self::FILE_META_TABLE_NAME)) {
            return $default;
        }

        $where = null;

        if (is_array($keys)) {
            foreach ($keys as $arrayKey => $item) {
                $keys[$arrayKey] = SQLite3::escapeString($item);
            }

            $where = " AND `key` IN ('" . implode("', '", $keys) . "')";
        }

        try {
            $query = $store->query(
                "SELECT * FROM " . self::FILE_META_TABLE_NAME . " WHERE " .
                "`filename`='" . SQLite3::escapeString($filename) . "' AND " .
                "`date`='" . SQLite3::escapeString(filemtime($path)) . "'" . $where
            );
        } catch (ExecuteError $exception) {
            return $default;
        }

        $returnList = [];

        while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
            $value = Json::decode($row['value']);

            if (json_last_error() === JSON_ERROR_NONE) {
                $returnList[$row['key']] = $value;
            } else {
                $returnList[$row['key']] = $row['value'];
            }
        }

        if (!count($returnList)) {
            return $default;
        }

        return $returnList;
    }

    /**
     * @param string $path
     * @param array $keys
     * @return bool
     * @throws ExecuteError
     * @throws ReadError
     */
    public function hasFileMetas(string $path, array $keys): bool
    {
        $dir = File::getDir($path);
        $filename = File::getFilename($path);

        $store = $this->getStoreToRead($dir);

        if (!$store->hasTable(self::FILE_META_TABLE_NAME)) {
            return false;
        }

        foreach ($keys as $arrayKey => $item) {
            $keys[$arrayKey] = SQLite3::escapeString($item);
        }

        $where = " WHERE `key` IN ('" . implode("', '", $keys) . "') AND `filename`='" . SQLite3::escapeString($filename) . "'";

        try {
            $count = $store->querySingle('SELECT COUNT(`key`) FROM ' . self::FILE_META_TABLE_NAME . $where);
        } catch (ExecuteError $e) {
            return false;
        }

        return count($keys) === (int)$count;
    }

    /**
     * @param string $path
     * @param string $key
     * @param mixed $value
     * @param null|string $checkSum
     * @throws ExecuteError
     * @throws WriteError
     */
    public function setFileMeta(string $path, string $key, $value, string $checkSum = null)
    {
        $dir = File::getDir($path);
        $filename = File::getFilename($path);
        $checkSum = $this->getChecksum($path, $checkSum);

        if (
            is_array($value) ||
            is_object($value)
        ) {
            $value = Json::encode($value);
        }

        $store = $this->getStoreToWrite($dir);
        $store->addTableIfNotExists(self::FILE_META_TABLE_NAME, self::FILE_META_CREATE_QUERY);

        $Query = $store->prepare(
            "REPLACE INTO " . self::FILE_META_TABLE_NAME .
            " VALUES(:checkSum, :filename, :date, :key, :value)"
        );
        $Query->bindValue(':checkSum', $checkSum, SQLITE3_TEXT);
        $Query->bindValue(':filename', $filename, SQLITE3_TEXT);
        $Query->bindValue(':date', filemtime($path), SQLITE3_INTEGER);
        $Query->bindValue(':key', $key, SQLITE3_TEXT);
        $Query->bindValue(':value', $value, SQLITE3_TEXT);

        if (!$Query->execute()) {
            throw new ExecuteError();
        }
    }

    /**
     * @param string $dir
     * @param array $values
     * @param null|string $checkSum
     * @throws ExecuteError
     * @throws WriteError
     */
    public function setFileMetas(string $dir, array $values, string $checkSum = null)
    {
        foreach ($values as $key => $value) {
            $this->setFileMeta($dir, $key, $value, $checkSum);
        }
    }

    /**
     * @param string $path
     * @param null|string $checkSum
     * @return bool
     * @throws ReadError
     * @throws ExecuteError
     */
    public function hasFileImage(string $path, string $checkSum = null): bool
    {
        $dir = File::getDir($path);
        $filename = File::getFilename($path);
        $store = $this->getStoreToRead($dir);

        if (!$store->hasTable(self::IMAGE_TABLE_NAME)) {
            return false;
        }

        $query = $store->querySingle(
            "SELECT chksum FROM " . self::IMAGE_TABLE_NAME . " WHERE " .
            "filename='" . SQLite3::escapeString($filename) . "' AND " .
            "date='" . SQLite3::escapeString(filemtime($path)) . "'"
        );

        if ($query) {
            return true;
        }

        $checkSum = $this->getChecksum($path, $checkSum);
        $query = $store->querySingle(
            "SELECT chksum FROM " . self::IMAGE_TABLE_NAME . " WHERE " .
            "chksum='" . SQLite3::escapeString($checkSum) . "'"
        );

        if ($query) {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     * @param int|null $width
     * @param int|null $height
     * @return Image
     * @throws ExecuteError
     * @throws FileNotFound
     * @throws ReadError
     * @throws WriteError
     */
    public function getFileImage(string $path, int $width = null, int $height = null): Image
    {
        $dir = File::getDir($path);
        $filename = File::getFilename($path);
        $store = $this->getStoreToWrite($dir);

        if (!$store->hasTable(self::IMAGE_TABLE_NAME)) {
            throw new ReadError('Tabelle ' . self::IMAGE_TABLE_NAME . ' existiert nicht!');
        }

        try {
            $query = $store->query(
                "SELECT * FROM " . self::IMAGE_TABLE_NAME . " WHERE " .
                "filename='" . SQLite3::escapeString($filename) . "'"
            );
        } catch (ExecuteError $e) {
            throw new ReadError();
        }

        $row = $query->fetchArray(SQLITE3_ASSOC);

        if ($row !== false) {
            $image = new Image();
            $image->load($row['image'], 'string');

            if (
                $width !== null ||
                $height !== null
            ) {
                if ($width > $image->getWidth()) {
                    $width = $image->getWidth();
                }

                if ($height > $image->getHeight()) {
                    $height = $image->getHeight();
                }

                $imageManipulate = ManipulateFactory::createByImage($image);
                $imageManipulate->resize(
                    $width ?? $image->getWidth(),
                    $height ?? $image->getHeight()
                );
            }

            return $image;
        }

        throw new ReadError('Bild existiert nicht!');
    }

    /**
     * @deprecated
     * @param string $path
     * @return Image|null
     * @throws WriteError
     * @throws FileNotFound
     * @throws ExecuteError
     */
    public function getThumbImage(string $path): ?Image
    {
        $dir = File::getDir($path);
        $filename = File::getFilename($path);
        $store = $this->getStoreToWrite($dir);

        if (!$store->hasTable(self::THUMBNAIL_TABLE_NAME)) {
            return null;
        }

        try {
            $query = $store->query(
                "SELECT * FROM " . self::THUMBNAIL_TABLE_NAME . " WHERE " .
                "filename='" . SQLite3::escapeString($filename) . "'"
            );
        } catch (ExecuteError $e) {
            return null;
        }

        $row = $query->fetchArray(SQLITE3_ASSOC);

        if ($row !== false) {
            $image = new Image();
            $image->load($row['image'], 'string');

            return $image;
        }

        return null;
    }

    /**
     * @param string $path
     * @param Image $image
     * @param null|string $checkSum
     * @throws ExecuteError
     * @throws WriteError
     */
    public function setFileImage(string $path, Image $image, string $checkSum = null)
    {
        $dir = File::getDir($path);
        $filename = File::getFilename($path);
        $store = $this->getStoreToWrite($dir);
        $store->addTableIfNotExists(self::IMAGE_TABLE_NAME, self::IMAGE_CREATE_QUERY);
        $checkSum = $this->getChecksum($path, $checkSum);

        $query = $store->prepare('REPLACE INTO ' . self::IMAGE_TABLE_NAME . ' VALUES(:chksum, :filename, :date, :image)');
        $query->bindValue(':chksum', $checkSum, SQLITE3_TEXT);
        $query->bindValue(':filename', $filename, SQLITE3_TEXT);
        $query->bindValue(':date', filemtime($path), SQLITE3_INTEGER);
        $query->bindValue(':image', $image->getString('jpg'), SQLITE3_BLOB);

        if (!$query->execute()) {
            throw new ExecuteError();
        }

        // @todo remove wenn explorer umgebaut ist
        $store->addTableIfNotExists(self::THUMBNAIL_TABLE_NAME, self::THUMBNAIL_CREATE_QUERY);

        $thumbnail = Thumbnail::createByImage($image);
        $thumbnail->create();

        $query = $store->prepare('REPLACE INTO ' . self::THUMBNAIL_TABLE_NAME . ' VALUES(:chksum, :filename, :date, :image)');
        $query->bindValue(':chksum', $checkSum, SQLITE3_TEXT);
        $query->bindValue(':filename', $filename, SQLITE3_TEXT);
        $query->bindValue(':date', filemtime($path), SQLITE3_INTEGER);
        $query->bindValue(':image', $thumbnail->getManipulate()->getImage()->getString('png'), SQLITE3_BLOB);

        if (!$query->execute()) {
            throw new ExecuteError();
        }
    }

    /**
     * @param string $dir
     * @param null|array $existingFiles
     * @throws ExecuteError
     * @throws WriteError
     */
    public function cleanStore(string $dir, array $existingFiles = null)
    {
        $this->cleanFileMetas($dir, $existingFiles);
        $this->cleanFileImages($dir, $existingFiles);
        $this->cleanFileThumbs($dir, $existingFiles);
    }

    /**
     * @param string $dir
     * @param null|array $existingFiles
     * @throws ExecuteError
     * @throws WriteError
     */
    public function cleanFileMetas(string $dir, array $existingFiles = null)
    {
        $store = $this->getStoreToWrite($dir);

        if (!$store->hasTable(self::FILE_META_TABLE_NAME)) {
            return;
        }

        if (is_null($existingFiles)) {
            $existingFiles = $this->getExistingFiles($dir);
        }

        foreach ($existingFiles as $key => $file) {
            $existingFiles[$key] = SQLite3::escapeString($file);
        }

        $store->execute(
            "DELETE FROM " . self::FILE_META_TABLE_NAME .
            " WHERE filename NOT IN ('" . implode("','", $existingFiles) . "')"
        );
    }

    /**
     * @param string $dir
     * @param null|array $existingFiles
     * @throws ExecuteError
     * @throws WriteError
     */
    public function cleanFileImages(string $dir, array $existingFiles = null)
    {
        $store = $this->getStoreToWrite($dir);

        if (!$store->hasTable(self::IMAGE_TABLE_NAME)) {
            return;
        }

        if (is_null($existingFiles)) {
            $existingFiles = $this->getExistingFiles($dir);
        }

        foreach ($existingFiles as $key => $file) {
            $existingFiles[$key] = SQLite3::escapeString($file);
        }

        $store->execute(
            "DELETE FROM " . self::IMAGE_TABLE_NAME .
            " WHERE filename NOT IN ('" . implode("','", $existingFiles) . "')"
        );
    }

    /**
     * @deprecated
     * @param string $dir
     * @param null|array $existingFiles
     * @throws ExecuteError
     * @throws WriteError
     */
    public function cleanFileThumbs(string $dir, array $existingFiles = null)
    {
        $store = $this->getStoreToWrite($dir);

        if (!$store->hasTable(self::THUMBNAIL_TABLE_NAME)) {
            return;
        }

        if (is_null($existingFiles)) {
            $existingFiles = $this->getExistingFiles($dir);
        }

        foreach ($existingFiles as $key => $file) {
            $existingFiles[$key] = SQLite3::escapeString($file);
        }

        $store->execute(
            "DELETE FROM " . self::THUMBNAIL_TABLE_NAME .
            " WHERE filename NOT IN ('" . implode("','", $existingFiles) . "')"
        );
    }

    /**
     * @param string $dir
     * @throws ExecuteError
     * @throws ReadError
     */
    public function close(string $dir)
    {
        $store = $this->getStoreToRead($dir);
        $store->close();
        unset($this->stores[$dir]);
    }

    /**
     * @param string $dir
     * @return array
     */
    private function getExistingFiles(string $dir): array
    {
        $existingFiles = [];

        foreach (glob(Dir::escapeForGlob($dir) . "*") as $path) {
            if (is_dir($path)) {
                continue;
            }

            $existingFiles[] = File::getFilename($path);
        }

        return $existingFiles;
    }

    /**
     * @param string $dir
     * @return SqLite
     * @throws ExecuteError
     * @throws WriteError
     */
    private function getStoreToWrite(string $dir): SqLite
    {
        if (!isset($this->stores[$dir])) {
            $this->stores[$dir] = SqLiteFactory::create($dir . '.gibsonStore');
            $this->stores[$dir]->busyTimeout(5000);
        }

        if (!$this->stores[$dir]->isWritable()) {
            throw new WriteError();
        }


        return $this->stores[$dir];
    }

    /**
     * @param string $dir
     * @return SqLite
     * @throws ExecuteError
     * @throws ReadError
     */
    private function getStoreToRead(string $dir): SqLite
    {
        if (!isset($this->stores[$dir])) {
            $this->stores[$dir] = SqLiteFactory::create($dir . '.gibsonStore');
            $this->stores[$dir]->busyTimeout(5000);
        }

        if (!$this->stores[$dir]->isReadable()) {
            throw new ReadError();
        }

        return $this->stores[$dir];
    }

    /**
     * @param string $filename
     * @param null|string $checkSum
     * @return string
     */
    private function getChecksum(string $filename, string $checkSum = null): string
    {
        if ($checkSum !== null) {
            return $checkSum;
        }

        return md5_file($filename);
    }
}