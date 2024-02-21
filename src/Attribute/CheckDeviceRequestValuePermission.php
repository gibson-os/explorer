<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Attribute;

use Attribute;
use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Module\Explorer\Service\Attribute\DeviceRequestValuePermissionAttribute;

#[Attribute]
class CheckDeviceRequestValuePermission extends CheckPermission
{
    public function __construct(
        array $permissions,
        array $permissionsByRequestValues = [],
        string $permissionParameter = 'userPermission',
        string $userParameter = 'permissionUser',
        private readonly string $deviceTokenRequestKey = 'deviceToken',
    ) {
        parent::__construct($permissions, $permissionsByRequestValues, $permissionParameter, $userParameter);
    }

    public function getAttributeServiceName(): string
    {
        return DeviceRequestValuePermissionAttribute::class;
    }

    public function getDeviceTokenRequestKey(): string
    {
        return $this->deviceTokenRequestKey;
    }
}
