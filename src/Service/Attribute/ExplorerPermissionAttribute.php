<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Attribute\GetSetting;
use GibsonOS\Core\Exception\LoginRequired;
use GibsonOS\Core\Exception\PermissionDenied;
use GibsonOS\Core\Exception\Repository\SelectError;
use GibsonOS\Core\Exception\RequestError;
use GibsonOS\Core\Model\Setting;
use GibsonOS\Core\Service\Attribute\AbstractActionAttributeService;
use GibsonOS\Core\Service\Attribute\PermissionAttribute;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\RequestService;
use GibsonOS\Module\Explorer\Attribute\CheckExplorerPermission;

class ExplorerPermissionAttribute extends AbstractActionAttributeService
{
    public function __construct(
        private readonly PermissionAttribute $permissionAttribute,
        private readonly RequestService $requestService,
        private readonly DirService $dirService,
        #[GetSetting('home_path')]
        private readonly Setting $homePath,
    ) {
    }

    /**
     * @throws PermissionDenied
     * @throws LoginRequired
     * @throws SelectError
     */
    public function preExecute(AttributeInterface $attribute, array $parameters, array $reflectionParameters): array
    {
        if (!$attribute instanceof CheckExplorerPermission) {
            return $parameters;
        }

        $homePath = $this->dirService->removeEndSlash($this->homePath->getValue());

        foreach ($attribute->getPathParameters() as $pathParameter) {
            try {
                $path = $this->requestService->getRequestValue($pathParameter);
            } catch (RequestError) {
                continue;
            }

            if (
                mb_strpos($homePath, $path) === 0
                && !($this->getReflectionParameter($pathParameter, $reflectionParameters)?->allowsNull() ?? true)
            ) {
                throw new PermissionDenied();
            }
        }

        $parameters = $this->permissionAttribute->preExecute($attribute, $parameters, $reflectionParameters);
        $parameters[$attribute->getHomePathParameter()] = $this->homePath->getValue();

        return $parameters;
    }

    public function usedParameters(AttributeInterface $attribute): array
    {
        if (!$attribute instanceof CheckExplorerPermission) {
            return [];
        }

        $usedParameters = $this->permissionAttribute->usedParameters($attribute);
        $usedParameters[] = $attribute->getHomePathParameter();

        return $usedParameters;
    }
}
