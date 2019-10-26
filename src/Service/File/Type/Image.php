<?php
namespace GibsonOS\Module\Explorer\Service\File\Type;

use GibsonOS\Core\Exception\FileNotFound;
use GibsonOS\Core\Service\Image as ImageService;

class Image implements FileTypeInterface
{
    private $imageSevice;

    public function __construct(ImageService $imageSevice)
    {
        $this->imageSevice = $imageSevice;
    }

    /**
     * @param string $filename
     * @return Image
     * @throws FileNotFound
     */
    public function getImage($filename)
    {
        return $this->imageSevice->load($filename);
    }

    /**
     * @param string $filename
     * @return array
     */
    public function getMetas($filename)
    {
        return [
            'width' => $this->imageSevice->getWidth(),
            'height' => $this->imageSevice->getHeight()
        ];
    }
}