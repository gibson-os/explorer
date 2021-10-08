<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File;

use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Exception\GetError;
use GibsonOS\Core\Service\ServiceManagerService;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;

class TypeFactory
{
    public function __construct(private DescriberFactory $describerFactory, private ServiceManagerService $serviceManagerService)
    {
    }

    /**
     * @throws FactoryError
     * @throws GetError
     */
    public function create(string $filename): FileTypeInterface
    {
        $fileTypeDescriber = $this->describerFactory->create($filename);
        /** @var FileTypeInterface $fileType */
        $fileType = $this->serviceManagerService->get($fileTypeDescriber->getServiceClassname());

        return $fileType;
    }
}
