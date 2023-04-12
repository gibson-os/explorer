<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\User\PermissionRepository;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use JsonException;
use ReflectionException;

class GeneralPermissionData extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManager $serviceManager,
        private readonly PermissionRepository $permissionRepository
    ) {
        parent::__construct($serviceManager);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     * @throws SaveError
     */
    public function install(string $module): Generator
    {
        try {
            $this->permissionRepository->getByModuleAndTask('explorer', 'middleware');
        } catch (SelectError) {
            $this->modelManager->save(
                (new Permission())
                    ->setModule('explorer')
                    ->setTask('middleware')
                    ->setPermission(Permission::READ + Permission::WRITE)
            );
        }

        yield new Success('Set general permission for explorer!');
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
