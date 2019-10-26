<?php
namespace GibsonOS\Module\Explorer\Factory\Html5;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Factory\Media as MediaFactory;
use GibsonOS\Module\Explorer\Model\Html5\Media as MediaModel;
use GibsonOS\Module\Explorer\Repository\Html5\Media as MediaRepository;
use GibsonOS\Module\Explorer\Service\Html5\Media as MediaService;

class Media
{
    /**
     * @param MediaModel $mediaModel
     * @return MediaService
     * @throws FileNotFound
     */
    public static function create(MediaModel $mediaModel)
    {
        $mediaService = MediaFactory::create($mediaModel->getDir() . $mediaModel->getFilename());

        return new MediaService($mediaModel, $mediaService);
    }

    /**
     * @param string $token
     * @return MediaService
     * @throws FileNotFound
     * @throws SelectError
     */
    public static function createByToken($token)
    {
        $mediaModel = MediaRepository::getByToken($token);

        return self::create($mediaModel);
    }


}