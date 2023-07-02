<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Enum\Permission;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Service\Attribute\AbstractActionAttributeService;
use GibsonOS\Core\Service\Attribute\MiddlewarePermissionAttributeService;
use GibsonOS\Core\Service\MiddlewareService;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Core\Utility\JsonUtility;
use GibsonOS\Module\Explorer\Attribute\CheckChromecastPermission;

class ChromecastPermissionAttribute extends AbstractActionAttributeService
{
    public function __construct(
        private readonly MiddlewarePermissionAttributeService $middlewarePermissionAttributeService,
        private readonly MiddlewareService $middlewareService,
        private readonly PermissionService $permissionService,
        private readonly RequestService $requestService,
    ) {
    }

    public function preExecute(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array
    {
        if (!$attribute instanceof CheckChromecastPermission) {
            return $parameters;
        }

        $parameters = $this->middlewarePermissionAttributeService->preExecute($attribute, $parameters, $reflectionParameters);
        $response = $this->middlewareService->send(
            'chromecast',
            'getSessionUserIds',
            ['id' => $this->requestService->getRequestValue('sessionId')]
        );

        $userIds = JsonUtility::decode($response->getBody()->getContent())['data'];
        $userIdsWithPermission = [];

        foreach ($userIds as $userId) {
            $permissionSum = array_sum(array_map(
                static fn (Permission $permission): int => $permission->value,
                $attribute->getPermissions(),
            ));
            $hasPermission = $this->permissionService->hasPermission(
                $permissionSum,
                'explorer',
                'html5',
                $this->requestService->getActionName(),
                $userId
            );

            if ($hasPermission) {
                $userIdsWithPermission[] = $userId;
            }
        }

        if (count($userIdsWithPermission) === 0) {
            throw new PermissionDenied();
        }

        $parameters[$attribute->getUserIdsParameter()] = $userIdsWithPermission;

        return $parameters;
    }

    public function usedParameters(AttributeInterface $attribute): array
    {
        if (!$attribute instanceof CheckChromecastPermission) {
            return [];
        }

        $usedParameters = $this->middlewarePermissionAttributeService->usedParameters($attribute);
        $usedParameters[] = $attribute->getUserIdsParameter();

        return $usedParameters;
    }
}
