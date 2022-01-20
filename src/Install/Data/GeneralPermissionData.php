<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;

class GeneralPermissionData extends AbstractInstall implements PriorityInterface
{
    /**
     * @throws SaveError
     */
    public function install(string $module): Generator
    {
        $this
            ->setPermission('savePosition')
            ->setPermission('video')
            ->setPermission('toSeeList')
            ->setPermission('image')
            ->setPermission('get')
            ->setPermission('chromecast')
            ->setPermission('audio')
        ;

        yield new Success('Set general permission for explorer!');
    }

    private function setPermission(string $action): GeneralPermissionData
    {
        (new Permission())
            ->setModule('explorer')
            ->setTask('html5')
            ->setAction($action)
            ->setPermission(Permission::READ)
            ->save()
        ;

        return $this;
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
