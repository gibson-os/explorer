<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Attribute;

use Attribute;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Module\Explorer\Service\Attribute\ExplorerPermissionAttribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CheckExplorerPermission extends CheckPermission
{
    /**
     * @param string[] $pathParameters
     */
    public function __construct(
        array $permissions,
        private readonly array $pathParameters = ['dir'],
        array $permissionsByRequestValues = [],
        string $permissionParameter = 'userPermission',
        string $userParameter = 'permissionUser',
        private readonly string $homePathParameter = 'homePath',
    ) {
        parent::__construct($permissions, $permissionsByRequestValues, $permissionParameter, $userParameter);
    }

    public function getAttributeServiceName(): string
    {
        return ExplorerPermissionAttribute::class;
    }

    /**
     * @return string[]
     */
    public function getPathParameters(): array
    {
        return $this->pathParameters;
    }

    public function getHomePathParameter(): string
    {
        return $this->homePathParameter;
    }
}
