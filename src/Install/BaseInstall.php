<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Install;

use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Service\Install\RequiredExtensionInterface;

class BaseInstall implements RequiredExtensionInterface
{
    public function checkRequiredExtensions(): void
    {
        if (!class_exists('ZipArchive')) {
            throw new InstallException('Please install PHP Zip extension!');
        }
    }
}
