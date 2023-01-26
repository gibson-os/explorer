<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Form\Html5;

use GibsonOS\Core\AutoComplete\UserAutoComplete;
use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Form\AbstractModelForm;
use GibsonOS\Core\Mapper\ModelMapper;

class ConnectedUserForm extends AbstractModelForm
{
    public function __construct(
        ModelMapper $modelMapper,
        private readonly UserAutoComplete $userAutoComplete,
    ) {
        parent::__construct($modelMapper);
    }

    protected function getFields(): array
    {
        return [
            'connectedUser' => new AutoCompleteParameter('Verbundener Benutzer', $this->userAutoComplete),
        ];
    }

    public function getButtons(): array
    {
        return [
            new Button('Speichern', 'explorer', 'html5', 'addConnectedUser'),
        ];
    }
}
