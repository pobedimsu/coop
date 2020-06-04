<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Deal;
use Symfony\Contracts\EventDispatcher\Event;

class DealEvent extends Event
{
    // Создание сделки
    const CREATED  = 'app.deal_created';

    // Обновление сделки
    const UPDATED  = 'app.deal_updated';

    // Отмена сделки
    const CANCELED = 'app.deal_canceled';

    // Отмена заявителем (покупателем)
    const CANCELED_BY_SELLER = 'app.deal_canceled_by_seller';

    // Отмена исполнителем (продавцом)
    const CANCELED_BY_DECLARANT  = 'app.deal_canceled_by_declarant';

    // Подтверждение сделки
    const CONFIRMED = 'app.deal_confirmed';

    protected $deal;

    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
    }

    public function getDeal(): Deal
    {
        return $this->deal;
    }
}
