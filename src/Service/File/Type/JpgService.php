<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Exception\Image\LoadError;

class JpgService extends ImageService
{
    /**
     * @param string $filename
     *
     * @throws LoadError
     * @throws GetError
     * @throws FileNotFound
     *
     * @return array
     */
    public function getMetas(string $filename): array
    {
        $exifReadData = exif_read_data($filename);

        if (!is_array($exifReadData)) {
            throw new GetError('Exif Daten für "%s" nicht gefunden!', $filename);
        }

        return array_merge(parent::getMetas($filename), $exifReadData);
    }
}
