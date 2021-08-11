<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\UserEvent;
use App\Service\TelegramService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    protected EntityManagerInterface $em;
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->telegram = $telegram;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvent::CONNECT_TELEGRAM    => 'sendConnectTgInviterNotify',
            UserEvent::DISCONNECT_TELEGRAM => 'sendDisconnectRgInviterNotify',
        ];
    }

    public function sendConnectTgInviterNotify(User $user): void
    {
        $text = 'Пользователь ' . $user . ' подключил телеграм';

        if ($user->getInvitedByUser()) {
            $this->telegram->sendMessage($user->getInvitedByUser(), $text);
        }
    }

    public function sendDisconnectRgInviterNotify(User $user): void
    {
        $text = 'Пользователь ' . $user . ' отключил телеграм';

        if ($user->getInvitedByUser()) {
            $this->telegram->sendMessage($user->getInvitedByUser(), $text);
        }
    }
}
