<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Install;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class HomePathInstall extends AbstractInstall implements PriorityInterface
{
    /**
     * @throws SaveError
     * @throws SelectError
     * @throws CreateError
     */
    public function install(string $module): Generator
    {
        yield $homePathInput = $this->getSettingInput(
            'explorer',
            'home_path',
            'What is the root directory for explorer?'
        );
        $value = $this->dirService->addEndSlash($homePathInput->getValue() ?? '');

        if (!file_exists($value)) {
            $this->dirService->create($value);
        }

        $this->setSetting('explorer', 'home_path', $value);

        yield new Success('Root directory is set!');
    }

    public function getPart(): string
    {
        return InstallService::PART_CONFIG;
    }

    public function getModule(): string
    {
        return 'explorer';
    }

    public function getPriority(): int
    {
        return 500;
    }
}
