<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\Html5;

use GibsonOS\Core\Service\MiddlewareService as CoreMiddlewareService;
use GibsonOS\Core\Service\PermissionService;
use GibsonOS\Core\Utility\JsonUtility;

class MiddlewareService
{
    public function __construct(
        private readonly PermissionService $permissionService,
        private readonly CoreMiddlewareService $middlewareService,
    ) {
    }

    public function getUserIds(string $sessionId): array
    {
        $response = $this->middlewareService->send('chromecast', 'getSessionUserIds', ['id' => $sessionId]);

        return JsonUtility::decode($response->getBody()->getContent())['data'];
    }

    public function checkPermission(array $userIds, string $action, int $permission): bool
    {
        foreach ($userIds as $userId) {
            if ($this->permissionService->hasPermission($permission, 'explorer', 'html5', $action, $userId)) {
                return true;
            }
        }

        return false;
    }
}
