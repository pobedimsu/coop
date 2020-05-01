<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\DealEvent;
use App\Service\TelegramService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DealSubscriber implements EventSubscriberInterface
{
    protected $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DealEvent::CREATED  => 'sendCreatedNotify',
            DealEvent::CANCELED_BY_CONTRACTOR => 'sendCanceledByContractorNotify',
            DealEvent::CANCELED_BY_DECLARANT  => 'sendCanceledByDeclarantNotify',
        ];
    }

    public function sendCreatedNotify(DealEvent $event): void
    {
        $deal = $event->getDeal();

        $text = 'У вас новая заявка на: ' . $deal->getOffer()->getTitle() . ' (кол-во ' . $deal->getOffer()->getQuantity() . ')';

        $this->telegram->sendMessage($deal->getDeclarantUser(), $text);
    }

    public function sendCanceledByContractorNotify(DealEvent $event): void
    {
        $deal = $event->getDeal();

        $text = 'Заявка отменена: ' . $deal->getOffer()->getTitle() . ' (кол-во ' . $deal->getOffer()->getQuantity() . ')';

        $this->telegram->sendMessage($deal->getDeclarantUser(), $text);
    }

    public function sendCanceledByDeclarantNotify(DealEvent $event): void
    {
        $deal = $event->getDeal();

        $text = 'Заявка отменена: ' . $deal->getOffer()->getTitle() . ' (кол-во ' . $deal->getOffer()->getQuantity() . ')';

        $this->telegram->sendMessage($deal->getContractorUser(), $text);
    }
}
