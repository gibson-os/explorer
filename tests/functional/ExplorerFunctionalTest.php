<?php
declare(strict_types=1);

namespace GibsonOS\Test\Functional\Explorer;

use GibsonOS\Test\Functional\Core\FunctionalTest;

class ExplorerFunctionalTest extends FunctionalTest
{
    protected function getDir(): string
    {
        return __DIR__;
    }

    protected function getDataDir(): string
    {
        return realpath($this->getDir() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '_data' . DIRECTORY_SEPARATOR);
    }
}
