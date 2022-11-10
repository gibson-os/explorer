<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Install;

use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\CreateError;
use GibsonOS\Core\Exception\InstallException;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class TrashInstall extends AbstractInstall implements PriorityInterface
{
    /**
     * @throws CreateError
     * @throws InstallException
     * @throws SaveError
     * @throws SelectError
     */
    public function install(string $module): \Generator
    {
        yield $trashDirInput = $this->getSettingInput(
            'explorer',
            'trashDir',
            'What is the directory for the trash?'
        );
        $dir = $this->dirService->addEndSlash($trashDirInput->getValue() ?? '');

        if (!file_exists($dir)) {
            $this->dirService->create($dir);
        }

        $this->setSetting('explorer', 'trashDir', $dir);

        yield $trashSizeInput = $this->getSettingInput(
            'explorer',
            'explorer_trash_size',
            'How big should the folder for the trash? (Possible specifications: 1024, 1kb, 1mb, 1gb, 0 = infinite)'
        );

        $this->setSetting('explorer', 'explorer_trash_size', $this->checkSizeInput($trashSizeInput));

        yield $trashLifetimeInput = $this->getSettingInput(
            'explorer',
            'trashLifetime',
            'How long should the files remain in the trash? (In days. 0 = infinite)'
        );
        $lifeTime = $trashLifetimeInput->getValue() ?? '';

        if (!is_numeric($lifeTime)) {
            throw new InstallException(sprintf('"%s" is no numeric value!', $lifeTime));
        }

        $this->setSetting('explorer', 'trashLifetime', $lifeTime);

        yield $trashCountInput = $this->getSettingInput(
            'explorer',
            'explorer_trash_count',
            'How many files should the trash be able to hold? (0 = infinite)'
        );
        $count = $trashCountInput->getValue() ?? '';

        if (!is_numeric($count)) {
            throw new InstallException(sprintf('"%s" is no numeric value!', $count));
        }

        $this->setSetting('explorer', 'explorer_trash_count', $count);

        yield new Success('Trash settings set!');
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
