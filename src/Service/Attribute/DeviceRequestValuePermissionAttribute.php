<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Exception\UserError;
use GibsonOS\Core\Service\Attribute\AbstractActionAttributeService;
use GibsonOS\Core\Service\Attribute\PermissionAttribute;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Service\UserService;
use GibsonOS\Module\Explorer\Attribute\CheckDeviceRequestValuePermission;

class DeviceRequestValuePermissionAttribute extends AbstractActionAttributeService
{
    public function __construct(
        private readonly PermissionAttribute $permissionAttribute,
        private readonly RequestService $requestService,
        private readonly UserService $userService,
    ) {
    }

    public function preExecute(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array
    {
        if (!$attribute instanceof CheckDeviceRequestValuePermission) {
            return $parameters;
        }

        try {
            $this->userService->deviceLogin(
                $this->requestService->getRequestValue($attribute->getDeviceTokenRequestKey()),
            );
        } catch (RequestError|UserError) {
        }

        return $this->permissionAttribute->preExecute($attribute, $parameters, $reflectionParameters);
    }
}
