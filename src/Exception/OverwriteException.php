<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Exception;

use GibsonOS\Core\Exception\AbstractException;

class OverwriteException extends AbstractException
{
    public function __construct(string $path, array $overwrite, array $ignore)
    {
        parent::__construct('Datei ' . $path . ' existierts bereits. Überschreiben?');

        $overwrite[] = $path;
        $ignore[] = $path;

        $this
            ->setTitle('Datei überschreiben?')
            ->setType(AbstractException::QUESTION)
            ->addButton('Überschreiben', 'overwrite[]', $overwrite)
            ->addButton('Alle Überschreiben', 'overwriteAll', true)
            ->addButton('Ignorieren', 'ignore[]', $ignore)
            ->addButton('Alle Ignorieren', 'ignoreAll', true)
            ->addButton('Abbrechen')
        ;
    }
}
