<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Dto\Image;
use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Exception\Image\LoadError;
use GibsonOS\Core\Service\ImageService as CoreImageService;

class ImageService implements FileTypeInterface
{
    public function __construct(private CoreImageService $imageService)
    {
    }

    /**
     * @throws FileNotFound
     * @throws LoadError
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
     * @throws FileNotFound
     * @throws LoadError
     */
    public function getImage(string $filename): Image
    {
        return $this->imageService->load($filename);
    }
}
