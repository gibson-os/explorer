<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Service\ImageService as CoreImageService;

class ImageService implements FileTypeInterface
{
    /**
     * @var CoreImageService
     */
    private $imageService;

    public function __construct(CoreImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * @param string $filename
     *
     * @throws FileNotFound
     * @throws LoadError
     *
     * @return array
     */
    public function getMetas(string $filename): array
    {
        $image = $this->getImage($filename);

        return [
            'width' => $this->imageService->getWidth($image),
            'height' => $this->imageService->getHeight($image),
        ];
    }

    /**
     * @param string $filename
     *
     * @throws FileNotFound
     * @throws LoadError
     *
     * @return Image
     */
    public function getImage(string $filename): Image
    {
        return $this->imageService->load($filename);
    }
}
