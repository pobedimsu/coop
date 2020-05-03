<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Invite;
use Symfony\Contracts\EventDispatcher\Event;

class InviteEvent extends Event
{
    // Регистрация по приглашению
    const REGISTER  = 'app.invite_register';

    protected $invite;

    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
    }

    public function getInvite(): Invite
    {
        return $this->invite;
    }
}
