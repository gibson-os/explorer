<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class AppData extends AbstractInstall implements PriorityInterface
{
    public function install(string $module): Generator
    {
        $this
            ->addApp('Explorer', 'explorer', 'index', 'index', 'icon_dir')
            ->addApp('Papierkorb', 'explorer', 'trash', 'index', 'icon_trash')
            ->addApp('HTML5 Medien', 'explorer', 'html5', 'index', 'icon_html5')
        ;

        yield new Success('Explorer apps installed!');
    }

    public function getPart(): string
    {
        return InstallService::PART_DATA;
    }

    public function getModule(): ?string
    {
        return 'explorer';
    }

    public function getPriority(): int
    {
        return 0;
    }
}
