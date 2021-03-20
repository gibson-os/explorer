<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Dto\Parameter;

use GibsonOS\Core\Dto\Parameter\AbstractParameter;

class DirectoryParameter extends AbstractParameter
{
    public function __construct(string $title = 'Verzeichnis')
    {
        parent::__construct($title, 'gosModuleExplorerDirParameter');
    }

    protected function getTypeConfig(): array
    {
        return [];
    }

    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_EQUAL,
            self::OPERATOR_NOT_EQUAL,
        ];
    }
}
