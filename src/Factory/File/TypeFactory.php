<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Factory\File;

use GibsonOS\Core\Exception\FactoryError;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Module\Explorer\Factory\File\Type\DescriberFactory;
use GibsonOS\Module\Explorer\Service\File\Type\FileTypeInterface;

class TypeFactory
{
    public function __construct(private DescriberFactory $describerFactory, private ServiceManager $ServiceManager)
    {
    }

    /**
     * @throws FactoryError
     */
    public function create(string $filename): FileTypeInterface
    {
        $fileTypeDescriber = $this->describerFactory->create($filename);
        /** @var FileTypeInterface $fileType */
        $fileType = $this->ServiceManager->get($fileTypeDescriber->getServiceClassname());

        return $fileType;
    }
}
