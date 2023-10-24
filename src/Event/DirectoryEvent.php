<?php
declare(strict_types=1);

namespace GibsonOS\Module\Explorer\Event;

use GibsonOS\Core\Attribute\Event;
use GibsonOS\Core\Dto\Fcm\Message;
use GibsonOS\Core\Dto\Parameter\StringParameter;
use GibsonOS\Core\Dto\Parameter\UserParameter;
use GibsonOS\Core\Event\AbstractEvent;
use GibsonOS\Core\Exception\FcmException;
use GibsonOS\Core\Exception\WebException;
use GibsonOS\Core\Manager\ReflectionManager;
use GibsonOS\Core\Model\User;
use GibsonOS\Core\Service\DirService;
use GibsonOS\Core\Service\EventService;
use GibsonOS\Core\Service\FcmService;
use GibsonOS\Module\Explorer\Dto\Parameter\DirectoryParameter;
use JsonException;

#[Event('Verzeichnis')]
class DirectoryEvent extends AbstractEvent
{
    public function __construct(
        EventService $eventService,
        ReflectionManager $reflectionManager,
        private readonly FcmService $fcmService,
        private readonly DirService $dirService,
    ) {
        parent::__construct($eventService, $reflectionManager);
    }

    /**
     * @throws FcmException
     * @throws WebException
     * @throws JsonException
     */
    #[Event\Method('Nachricht senden')]
    public function pushMessage(
        #[Event\Parameter(UserParameter::class)]
        User $user,
        #[Event\Parameter(DirectoryParameter::class)]
        string $directory,
        #[Event\Parameter(StringParameter::class, 'Titel')]
        ?string $title,
        #[Event\Parameter(StringParameter::class, 'Text')]
        ?string $body,
    ): void {
        foreach ($user->getDevices() as $device) {
            $token = $device->getToken();
            $fcmToken = $device->getFcmToken();

            if ($token === null || $fcmToken === null) {
                continue;
            }

            $this->fcmService->pushMessage(new Message(
                $token,
                $fcmToken,
                title: $title,
                body: $body,
                module: 'explorer',
                task: 'index',
                action: 'index',
                data: ['directory' => $directory],
            ));
        }
    }
}
