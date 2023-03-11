<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Attribute;

use GibsonOS\Core\Attribute\CheckPermission;
use GibsonOS\Module\Explorer\Service\Attribute\ChromecastPermissionAttribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class CheckChromecastPermission extends CheckPermission
{
    public function __construct(
        int $permission,
        array $permissionsByRequestValues = [],
        string $permissionParameter = 'userPermission',
        private readonly string $userIdsParameter = 'userIds',
    ) {
        parent::__construct($permission, $permissionsByRequestValues, $permissionParameter);
    }

    public function getAttributeServiceName(): string
    {
        return ChromecastPermissionAttribute::class;
    }

    public function getUserIdsParameter(): string
    {
        return $this->userIdsParameter;
    }
}
