<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Form\Html5;

use GibsonOS\Core\AutoComplete\UserAutoComplete;
use GibsonOS\Core\Dto\Form\AbstractModelConfig;
use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Form\AbstractModelForm;

class ConnectedUserForm extends AbstractModelForm
{
    public function __construct(private readonly UserAutoComplete $userAutoComplete)
    {
    }

    protected function getFields(AbstractModelConfig $config): array
    {
        return [
            'connectedUserId' => new AutoCompleteParameter('Verbundener Benutzer', $this->userAutoComplete),
        ];
    }

    public function getButtons(AbstractModelConfig $config): array
    {
        return [
            'save' => new Button('Speichern', 'explorer', 'html5', 'addConnectedUser'),
        ];
    }
}
