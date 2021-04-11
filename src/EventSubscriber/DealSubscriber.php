<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Deal;
use App\Event\DealEvent;
use App\Service\TelegramService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DealSubscriber implements EventSubscriberInterface
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DealEvent::CREATED  => 'sendCreatedNotify',
            DealEvent::UPDATED  => 'sendUpdatedNotify',
            DealEvent::CANCELED_BY_SELLER => 'sendCanceledBySellerNotify',
            DealEvent::CANCELED_BY_BUYER  => 'sendCanceledByBuyerNotify',
        ];
    }

    public function sendCreatedNotify(Deal $deal): void
    {
        $text = 'У вас новая заявка на: ' . $deal->getOffer()->getTitle() . ' (кол-во ' . $deal->getQuantity() . ')';

        $this->telegram->sendMessage($deal->getSeller(), $text);
    }

    public function sendUpdatedNotify(Deal $deal): void
    {
        $text = 'Изменение запроса на: ' . $deal->getOffer()->getTitle() . ' (кол-во ' . $deal->getQuantity() . ')';

        $this->telegram->sendMessage($deal->getSeller(), $text);
    }

    public function sendCanceledBySellerNotify(Deal $deal): void
    {
        $text = 'Заявка отменена: ' . $deal->getOffer()->getTitle() . ' (кол-во ' . $deal->getQuantity() . ')';

        $this->telegram->sendMessage($deal->getBuyer(), $text);
    }

    public function sendCanceledByBuyerNotify(Deal $deal): void
    {
        $text = 'Заявка отменена: ' . $deal->getOffer()->getTitle() . ' (кол-во ' . $deal->getQuantity() . ')';

        $this->telegram->sendMessage($deal->getSeller(), $text);
    }
}
