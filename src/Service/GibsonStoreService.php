<?php
/** @noinspection SqlNoDataSourceInspection */
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service;

use Exception;
use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\CreateError;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Exception\Sqlite\ExecuteError;
use GibsonOS\Core\Exception\Sqlite\ReadError;
use GibsonOS\Core\Exception\Sqlite\WriteError;
use GibsonOS\Core\Factory\SqLiteFactory;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\FileService;
use GibsonOS\Core\Service\Image\ThumbnailService;
use GibsonOS\Core\Service\SqLiteService;
use GibsonOS\Core\Utility\JsonUtility;
use JsonException;
use SQLite3;
use SQLite3Result;

class GibsonStoreService
{
    public const META_TABLE_NAME = 'meta';

    public const META_CREATE_QUERY = 'CREATE TABLE meta (`key` varchar(32), `value` text, PRIMARY KEY (`key`))';

    public const FILE_META_TABLE_NAME = 'fileMeta';

    public const FILE_META_CREATE_QUERY = 'CREATE TABLE fileMeta (`chksum` varchar(32), `filename` varchar(255), `date` int, `key` varchar(32), `value` text, PRIMARY KEY (`key`, `filename`))';

    public const IMAGE_TABLE_NAME = 'image';

    public const IMAGE_CREATE_QUERY = 'CREATE TABLE image (`chksum` varchar(32), `filename` varchar(255), `date` int, `image` blob, PRIMARY KEY (`chksum`))';

    public const THUMBNAIL_TABLE_NAME = 'thumb';

    public const THUMBNAIL_CREATE_QUERY = 'CREATE TABLE thumb (`chksum` varchar(32), `filename` varchar(255), `date` int, `image` blob, PRIMARY KEY (`chksum`))';

    /**
     * @var SqLiteService[]
     */
    private array $stores = [];

    public function __construct(
        private FileService $fileService,
        private DirService $dirService,
        private ThumbnailService $thumbnailService,
        private SqLiteFactory $sqLiteFactory,
    ) {
    }

    /**
     * @param mixed|null $default
     *
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
                'SELECT * FROM ' . self::META_TABLE_NAME .
                " WHERE `key`='" . SQLite3::escapeString($key) . "'",
            );
        } catch (ExecuteError) {
            return $default;
        }

        $row = $query->fetchArray(SQLITE3_ASSOC);

        if ($row !== false) {
            return $row['value'];
        }

        return $default;
    }

    /**
     * @param mixed|null $default
     *
     * @throws ExecuteError
     * @throws ReadError
     */
    public function getDirMetas(string $dir, ?array $keys = null, $default = null)
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
            $query = $store->query('SELECT * FROM ' . self::META_TABLE_NAME . $where);
        } catch (ExecuteError) {
            return $default;
        }

        $returnList = [];

        while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
            $returnList[$row['key']] = $row['value'];
        }

        if ($returnList === []) {
            return $default;
        }

        return $returnList;
    }

    /**
     * @throws WriteError
     * @throws ExecuteError
     */
    public function setDirMeta(string $dir, string $key, $value): GibsonStoreService
    {
        $store = $this->getStoreToWrite($dir);
        $store->addTableIfNotExists(self::META_TABLE_NAME, self::META_CREATE_QUERY);
        $store->execute('REPLACE INTO ' . self::META_TABLE_NAME . " VALUES('" . SQLite3::escapeString($key) . "', '" . (is_numeric($value) ? $value : SQLite3::escapeString($value)) . "')");

        return $this;
    }

    /**
     * @throws ExecuteError
     * @throws WriteError
     */
    public function setDirMetas(string $dir, array $values): GibsonStoreService
    {
        foreach ($values as $key => $value) {
            $this->setDirMeta($dir, $key, $value);
        }

        return $this;
    }

    /**
     * @param mixed|null $default
     *
     * @throws ExecuteError
     * @throws ReadError
     */
    public function getFileMeta(string $path, string $key, $default = null)
    {
        $dir = $this->fileService->getDir($path);
        $filename = $this->fileService->getFilename($path);

        $store = $this->getStoreToRead($dir);

        if (!$store->hasTable(self::FILE_META_TABLE_NAME)) {
            return $default;
        }

        try {
            $query = $store->query(
                'SELECT * FROM ' . self::FILE_META_TABLE_NAME . ' WHERE ' .
                "`filename`='" . SQLite3::escapeString($filename) . "' AND " .
                "`date`='" . SQLite3::escapeString((string) filemtime($path)) . "' AND `key`='" . SQLite3::escapeString($key) . "'",
            );
        } catch (ExecuteError) {
            return $default;
        }

        $row = $query->fetchArray(SQLITE3_ASSOC);

        if ($row !== false) {
            try {
                return JsonUtility::decode($row['value']);
            } catch (JsonException) {
                return $row['value'];
            }
        }

        return $default;
    }

    /**
     * @param mixed|null $default
     *
     * @throws ReadError
     * @throws ExecuteError
     */
    public function getFileMetas(string $path, ?array $keys = null, $default = null)
    {
        $dir = $this->fileService->getDir($path);
        $filename = $this->fileService->getFilename($path);

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
                'SELECT * FROM ' . self::FILE_META_TABLE_NAME . ' WHERE ' .
                "`filename`='" . SQLite3::escapeString($filename) . "' AND " .
                "`date`='" . SQLite3::escapeString((string) filemtime($path)) . "'" . $where,
            );
        } catch (ExecuteError) {
            return $default;
        }

        $returnList = [];

        while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
            try {
                $returnList[$row['key']] = JsonUtility::decode($row['value']);
            } catch (JsonException) {
                $returnList[$row['key']] = $row['value'];
            }
        }

        if ($returnList === []) {
            return $default;
        }

        return $returnList;
    }

    /**
     * @throws ExecuteError
     * @throws ReadError
     */
    public function hasFileMetas(string $path, array $keys): bool
    {
        $dir = $this->fileService->getDir($path);
        $filename = $this->fileService->getFilename($path);

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
        } catch (ExecuteError) {
            return false;
        }

        return count($keys) === (int) $count;
    }

    /**
     * @throws ExecuteError
     * @throws GetError
     * @throws JsonException
     * @throws WriteError
     */
    public function setFileMeta(string $path, string $key, $value, ?string $checkSum = null): GibsonStoreService
    {
        $dir = $this->fileService->getDir($path);
        $filename = $this->fileService->getFilename($path);
        $checkSum = $this->getChecksum($path, $checkSum);

        if (
            is_array($value)
            || is_object($value)
        ) {
            $value = JsonUtility::encode($value);
        }

        $store = $this->getStoreToWrite($dir);
        $store->addTableIfNotExists(self::FILE_META_TABLE_NAME, self::FILE_META_CREATE_QUERY);

        $query = $store->prepare(
            'REPLACE INTO ' . self::FILE_META_TABLE_NAME .
            ' VALUES(:checkSum, :filename, :date, :key, :value)',
        );
        $query->bindValue(':checkSum', $checkSum, SQLITE3_TEXT);
        $query->bindValue(':filename', $filename, SQLITE3_TEXT);
        $query->bindValue(':date', filemtime($path), SQLITE3_INTEGER);
        $query->bindValue(':key', $key, SQLITE3_TEXT);
        $query->bindValue(':value', $value, SQLITE3_TEXT);

        if (!$query->execute() instanceof SQLite3Result) {
            throw new ExecuteError();
        }

        return $this;
    }

    /**
     * @throws ExecuteError
     * @throws GetError
     * @throws JsonException
     * @throws WriteError
     */
    public function setFileMetas(string $dir, array $values, ?string $checkSum = null): GibsonStoreService
    {
        foreach ($values as $key => $value) {
            $this->setFileMeta($dir, $key, $value, $checkSum);
        }

        return $this;
    }

    /**
     * @throws ExecuteError
     * @throws GetError
     * @throws ReadError
     */
    public function hasFileImage(string $path, ?string $checkSum = null): bool
    {
        $dir = $this->fileService->getDir($path);
        $filename = $this->fileService->getFilename($path);
        $store = $this->getStoreToRead($dir);

        if (!$store->hasTable(self::IMAGE_TABLE_NAME)) {
            return false;
        }

        $query = $store->querySingle(
            'SELECT chksum FROM ' . self::IMAGE_TABLE_NAME . ' WHERE ' .
            "filename='" . SQLite3::escapeString($filename) . "' AND " .
            "date='" . SQLite3::escapeString((string) filemtime($path)) . "'",
        );

        if ($query) {
            return true;
        }

        $checkSum = $this->getChecksum($path, $checkSum);
        $query = $store->querySingle(
            'SELECT chksum FROM ' . self::IMAGE_TABLE_NAME . ' WHERE ' .
            "chksum='" . SQLite3::escapeString($checkSum) . "'",
        );

        return (bool) $query;
    }

    /**
     * @throws ExecuteError
     * @throws FileNotFound
     * @throws ReadError
     * @throws WriteError
     * @throws LoadError
     * @throws CreateError
     */
    public function getFileImage(string $path, ?int $width = null, ?int $height = null): Image
    {
        $dir = $this->fileService->getDir($path);
        $filename = $this->fileService->getFilename($path);
        $store = $this->getStoreToWrite($dir);

        if (!$store->hasTable(self::IMAGE_TABLE_NAME)) {
            throw new ReadError('Tabelle ' . self::IMAGE_TABLE_NAME . ' existiert nicht!');
        }

        try {
            $query = $store->query(
                'SELECT * FROM ' . self::IMAGE_TABLE_NAME . ' WHERE ' .
                "filename='" . SQLite3::escapeString($filename) . "'",
            );
        } catch (ExecuteError) {
            throw new ReadError();
        }

        $row = $query->fetchArray(SQLITE3_ASSOC);

        if ($row !== false) {
            $image = $this->thumbnailService->load($row['image'], 'string');

            if (
                $width !== null
                || $height !== null
            ) {
                if ($width > $this->thumbnailService->getWidth($image)) {
                    $width = $this->thumbnailService->getWidth($image);
                }

                if ($height > $this->thumbnailService->getHeight($image)) {
                    $height = $this->thumbnailService->getHeight($image);
                }

                $this->thumbnailService->resize(
                    $image,
                    $width ?? $this->thumbnailService->getWidth($image),
                    $height ?? $this->thumbnailService->getHeight($image),
                );
            }

            return $image;
        }

        throw new ReadError('Bild existiert nicht!');
    }

    /**
     * @throws FileNotFound
     * @throws ExecuteError
     * @throws LoadError
     * @throws WriteError
     *
     * @deprecated
     */
    public function getThumbImage(string $path): ?Image
    {
        $dir = $this->fileService->getDir($path);
        $filename = $this->fileService->getFilename($path);
        $store = $this->getStoreToWrite($dir);

        if (!$store->hasTable(self::THUMBNAIL_TABLE_NAME)) {
            return null;
        }

        try {
            $query = $store->query(
                'SELECT * FROM ' . self::THUMBNAIL_TABLE_NAME . ' WHERE ' .
                "filename='" . SQLite3::escapeString($filename) . "'",
            );
        } catch (ExecuteError) {
            return null;
        }

        $row = $query->fetchArray(SQLITE3_ASSOC);

        if ($row !== false) {
            return $this->thumbnailService->load($row['image'], 'string');
        }

        return null;
    }

    /**
     * @throws ExecuteError
     * @throws GetError
     * @throws WriteError
     * @throws CreateError
     */
    public function setFileImage(string $path, Image $image, ?string $checkSum = null): GibsonStoreService
    {
        $dir = $this->fileService->getDir($path);
        $filename = $this->fileService->getFilename($path);
        $store = $this->getStoreToWrite($dir);
        $store->addTableIfNotExists(self::IMAGE_TABLE_NAME, self::IMAGE_CREATE_QUERY);
        $checkSum = $this->getChecksum($path, $checkSum);

        $query = $store->prepare('REPLACE INTO ' . self::IMAGE_TABLE_NAME . ' VALUES(:chksum, :filename, :date, :image)');
        $query->bindValue(':chksum', $checkSum, SQLITE3_TEXT);
        $query->bindValue(':filename', $filename, SQLITE3_TEXT);
        $query->bindValue(':date', filemtime($path), SQLITE3_INTEGER);
        $query->bindValue(':image', $this->thumbnailService->getString($image), SQLITE3_BLOB);

        if (!$query->execute() instanceof SQLite3Result) {
            throw new ExecuteError();
        }

        // @todo remove wenn explorer umgebaut ist
        $store->addTableIfNotExists(self::THUMBNAIL_TABLE_NAME, self::THUMBNAIL_CREATE_QUERY);

        $thumbnail = $this->thumbnailService->generate($image);

        $query = $store->prepare('REPLACE INTO ' . self::THUMBNAIL_TABLE_NAME . ' VALUES(:chksum, :filename, :date, :image)');
        $query->bindValue(':chksum', $checkSum, SQLITE3_TEXT);
        $query->bindValue(':filename', $filename, SQLITE3_TEXT);
        $query->bindValue(':date', filemtime($path), SQLITE3_INTEGER);
        $query->bindValue(':image', $this->thumbnailService->getString($thumbnail, 'png'), SQLITE3_BLOB);

        if (!$query->execute() instanceof SQLite3Result) {
            throw new ExecuteError();
        }

        return $this;
    }

    /**
     * @throws ExecuteError
     * @throws GetError
     * @throws WriteError
     */
    public function cleanStore(string $dir, ?array $existingFiles = null): GibsonStoreService
    {
        $this
            ->cleanFileMetas($dir, $existingFiles)
            ->cleanFileImages($dir, $existingFiles)
            ->cleanFileThumbs($dir, $existingFiles)
        ;

        return $this;
    }

    /**
     * @throws ExecuteError
     * @throws GetError
     * @throws WriteError
     */
    public function cleanFileMetas(string $dir, ?array $existingFiles = null): GibsonStoreService
    {
        $store = $this->getStoreToWrite($dir);

        if (!$store->hasTable(self::FILE_META_TABLE_NAME)) {
            return $this;
        }

        if ($existingFiles === null) {
            $existingFiles = $this->getExistingFiles($dir);
        }

        foreach ($existingFiles as $key => $file) {
            $existingFiles[$key] = SQLite3::escapeString($file);
        }

        $store->execute(
            'DELETE FROM ' . self::FILE_META_TABLE_NAME .
            " WHERE filename NOT IN ('" . implode("','", $existingFiles) . "')",
        );

        return $this;
    }

    /**
     * @throws ExecuteError
     * @throws WriteError
     * @throws GetError
     */
    public function cleanFileImages(string $dir, ?array $existingFiles = null): GibsonStoreService
    {
        $store = $this->getStoreToWrite($dir);

        if (!$store->hasTable(self::IMAGE_TABLE_NAME)) {
            return $this;
        }

        if ($existingFiles === null) {
            $existingFiles = $this->getExistingFiles($dir);
        }

        foreach ($existingFiles as $key => $file) {
            $existingFiles[$key] = SQLite3::escapeString($file);
        }

        $store->execute(
            'DELETE FROM ' . self::IMAGE_TABLE_NAME .
            " WHERE filename NOT IN ('" . implode("','", $existingFiles) . "')",
        );

        return $this;
    }

    /**
     * @throws ExecuteError
     * @throws WriteError
     * @throws GetError
     *
     * @deprecated
     */
    public function cleanFileThumbs(string $dir, ?array $existingFiles = null): GibsonStoreService
    {
        $store = $this->getStoreToWrite($dir);

        if (!$store->hasTable(self::THUMBNAIL_TABLE_NAME)) {
            return $this;
        }

        if ($existingFiles === null) {
            $existingFiles = $this->getExistingFiles($dir);
        }

        foreach ($existingFiles as $key => $file) {
            $existingFiles[$key] = SQLite3::escapeString($file);
        }

        $store->execute(
            'DELETE FROM ' . self::THUMBNAIL_TABLE_NAME .
            " WHERE filename NOT IN ('" . implode("','", $existingFiles) . "')",
        );

        return $this;
    }

    /**
     * @throws ExecuteError
     * @throws ReadError
     */
    public function close(string $dir): GibsonStoreService
    {
        $store = $this->getStoreToRead($dir);
        $store->close();
        unset($this->stores[$dir]);

        return $this;
    }

    /**
     * @throws GetError
     */
    private function getExistingFiles(string $dir): array
    {
        $existingFiles = [];

        foreach ($this->dirService->getFiles($dir) as $path) {
            if (is_dir($path)) {
                continue;
            }

            $existingFiles[] = $this->fileService->getFilename($path);
        }

        return $existingFiles;
    }

    /**
     * @throws ExecuteError
     * @throws WriteError
     */
    private function getStoreToWrite(string $dir): SqLiteService
    {
        $dir = $this->dirService->addEndSlash($dir);

        if (!isset($this->stores[$dir])) {
            try {
                $this->stores[$dir] = $this->sqLiteFactory->create($dir . '.gibsonStore');
            } catch (Exception $exception) {
                throw new ExecuteError(sprintf('Cant create database for %s to write', $dir), 0, $exception);
            }

            $this->stores[$dir]->busyTimeout(5000);
        }

        if (!$this->stores[$dir]->isWritable()) {
            throw new WriteError();
        }

        return $this->stores[$dir];
    }

    /**
     * @throws ExecuteError
     * @throws ReadError
     */
    private function getStoreToRead(string $dir): SqLiteService
    {
        $dir = $this->dirService->addEndSlash($dir);

        if (!isset($this->stores[$dir])) {
            try {
                $this->stores[$dir] = $this->sqLiteFactory->create($dir . '.gibsonStore');
            } catch (Exception $exception) {
                throw new ExecuteError(sprintf('Cant create database for %s to read', $dir), 0, $exception);
            }

            $this->stores[$dir]->busyTimeout(5000);
        }

        if (!$this->stores[$dir]->isReadable()) {
            throw new ReadError();
        }

        return $this->stores[$dir];
    }

    /**
     * @throws GetError
     */
    private function getChecksum(string $filename, ?string $checkSum = null): string
    {
        if ($checkSum === null) {
            $checkSum = md5_file($filename);

            if ($checkSum === false) {
                throw new GetError(sprintf('Checksumme für "%s" nicht ermittelt!', $filename));
            }
        }

        return $checkSum;
    }
}
