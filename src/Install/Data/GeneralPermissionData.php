<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Install\Data;

use Generator;
use GibsonOS\Core\Dto\Install\Success;
use GibsonOS\Core\Enum\Permission as PermissionEnum;
use GibsonOS\Core\Exception\Model\SaveError;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Install\AbstractInstall;
use GibsonOS\Core\Manager\ServiceManager;
use GibsonOS\Core\Model\User\Permission;
use GibsonOS\Core\Repository\TaskRepository;
use GibsonOS\Core\Repository\User\PermissionRepository;
use GibsonOS\Core\Service\InstallService;
use GibsonOS\Core\Service\PriorityInterface;
use JsonException;
use MDO\Exception\ClientException;
use MDO\Exception\RecordException;
use ReflectionException;

class GeneralPermissionData extends AbstractInstall implements PriorityInterface
{
    public function __construct(
        ServiceManager $serviceManager,
        private readonly PermissionRepository $permissionRepository,
        private readonly TaskRepository $taskRepository,
    ) {
        parent::__construct($serviceManager);
    }

    /**
     * @throws ReflectionException
     * @throws SaveError
     * @throws SelectError
     * @throws JsonException
     * @throws ClientException
     * @throws RecordException
     */
    public function install(string $module): Generator
    {
        $module = $this->moduleRepository->getByName('explorer');
        $task = $this->taskRepository->getByNameAndModuleId('middleware', $module->getId() ?? 0);

        try {
            $this->permissionRepository->getByModuleAndTask($module, $task);
        } catch (SelectError) {
            $this->modelManager->save(
                (new Permission($this->modelWrapper))
                    ->setModule($module)
                    ->setTask($task)
                    ->setPermission(PermissionEnum::READ->value + PermissionEnum::WRITE->value)
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
