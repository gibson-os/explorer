<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Utility\File;

class TypeUtility
{
    private const CATEGORY_IMAGE = 1;

    private const CATEGORY_VIDEO = 2;

    private const CATEGORY_PDF = 3;

    private const CATEGORY_AUDIO = 4;

    private const CATEGORY_OFFICE = 5;

    private const CATEGORY_ARCHIVE = 6;

    private const CATEGORY_BINARY = 7;

    private const CATEGORY_TEXT = 8;

    private const EXTENSIONS_CATEGORY = [
        'jpg' => self::CATEGORY_IMAGE,
        'jpeg' => self::CATEGORY_IMAGE,
        'gif' => self::CATEGORY_IMAGE,
        'png' => self::CATEGORY_IMAGE,
        'bmp' => self::CATEGORY_IMAGE,
        'asf' => self::CATEGORY_VIDEO,
        'avi' => self::CATEGORY_VIDEO,
        'mkv' => self::CATEGORY_VIDEO,
        'mpg' => self::CATEGORY_VIDEO,
        'mpeg' => self::CATEGORY_VIDEO,
        'ogg' => self::CATEGORY_VIDEO,
        'fla' => self::CATEGORY_VIDEO,
        'swf' => self::CATEGORY_VIDEO,
        'flv' => self::CATEGORY_VIDEO,
        'f4v' => self::CATEGORY_VIDEO,
        'f4p' => self::CATEGORY_VIDEO,
        'mp4' => self::CATEGORY_VIDEO,
        'mov' => self::CATEGORY_VIDEO,
        '3gp' => self::CATEGORY_VIDEO,
        'wmv' => self::CATEGORY_VIDEO,
        'rm' => self::CATEGORY_VIDEO,
        'webm' => self::CATEGORY_VIDEO,
        'wav' => self::CATEGORY_AUDIO,
        'mp3' => self::CATEGORY_AUDIO,
        'm4a' => self::CATEGORY_AUDIO,
        'f4a' => self::CATEGORY_AUDIO,
        'f4b' => self::CATEGORY_AUDIO,
        'aiff' => self::CATEGORY_AUDIO,
        'pdf' => self::CATEGORY_PDF,
        'odt' => self::CATEGORY_OFFICE,
        'doc' => self::CATEGORY_OFFICE,
        'docx' => self::CATEGORY_OFFICE,
        'ods' => self::CATEGORY_OFFICE,
        'xls' => self::CATEGORY_OFFICE,
        'opd' => self::CATEGORY_OFFICE,
        'ppt' => self::CATEGORY_OFFICE,
        'pptx' => self::CATEGORY_OFFICE,
        'odg' => self::CATEGORY_OFFICE,
        'rar' => self::CATEGORY_ARCHIVE,
        'zip' => self::CATEGORY_ARCHIVE,
        'exe' => self::CATEGORY_BINARY,
        'bin' => self::CATEGORY_BINARY,
        'iso' => self::CATEGORY_BINARY,
        'txt' => self::CATEGORY_TEXT,
        'js' => self::CATEGORY_TEXT,
        'php' => self::CATEGORY_TEXT,
        'html' => self::CATEGORY_TEXT,
        'htm' => self::CATEGORY_TEXT,
    ];

    public function getCategory(string $path): ?int
    {
        $extension = $this->getExtension($path);

        if (
            $extension === null ||
            !isset(self::EXTENSIONS_CATEGORY[$extension])
        ) {
            return null;
        }

        return self::EXTENSIONS_CATEGORY[$extension];
    }

    public function getExtension(string $path): ?string
    {
        if (mb_strrpos($path, '.') === false) {
            return null;
        }

        return mb_substr($path, mb_strrpos($path, '.') + 1);
    }
}
