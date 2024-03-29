<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Form\Html5;

use GibsonOS\Core\AutoComplete\UserAutoComplete;
use GibsonOS\Core\Dto\Form\Button;
use GibsonOS\Core\Dto\Form\ModelFormConfig;
use GibsonOS\Core\Dto\Parameter\AutoCompleteParameter;
use GibsonOS\Core\Form\AbstractModelForm;
use GibsonOS\Module\Explorer\Model\Html5\ConnectedUser;

/**
 * @extends AbstractModelForm<ConnectedUser>
 */
class ConnectedUserForm extends AbstractModelForm
{
    public function __construct(private readonly UserAutoComplete $userAutoComplete)
    {
    }

    protected function getFields(ModelFormConfig $config): array
    {
        return [
            'connectedUserId' => new AutoCompleteParameter('Verbundener Benutzer', $this->userAutoComplete),
        ];
    }

    public function getButtons(ModelFormConfig $config): array
    {
        return [
            'save' => new Button('Speichern', 'explorer', 'html5', 'addConnectedUser'),
        ];
    }

    protected function supportedModel(): string
    {
        return ConnectedUser::class;
    }
}
