<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Invite;
use App\Entity\User;
use App\Event\InviteEvent;
use App\Service\TelegramService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InviteSubscriber implements EventSubscriberInterface
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
            InviteEvent::REGISTER  => 'sendRegisterNotify',
        ];
    }

    public function sendRegisterNotify(Invite $invite): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['invite' => $invite]);

        $text = 'По ссылке-приглашению зарегистрировался новый участник: ' . $user;

        $this->telegram->sendMessage($invite->getUser(), $text);
    }
}
