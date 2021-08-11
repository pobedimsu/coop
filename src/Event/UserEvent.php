<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event
{
    const CONNECT_TELEGRAM    = 'app.user_connect_telegram';
    const DISCONNECT_TELEGRAM = 'app.user_disconnect_telegram';

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
