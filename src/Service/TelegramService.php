<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Borsaco\TelegramBotApiBundle\Service\Bot;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramService
{
    protected Api $tg;
    protected Bot $bot;

    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
        $this->tg = $bot->getBot();
    }

    public function sendMessage(User $user, string $text): void
    {
        if (!$user->getTelegramUserId()) {
            return;
        }

        $count = 2;

        Try_Send_Message:

        try {
            $this->tg->sendMessage([
                'chat_id' => $user->getTelegramUserId(),
                'text'    => $text,
            ]);
        } catch (TelegramSDKException $e) {
            if ($count--) {
                usleep(300);

                goto Try_Send_Message;
            }
        }
    }

    public function isEnable(): bool
    {
        if ($this->tg->getAccessToken() === '~'
            or $this->tg->getAccessToken() === 'null'
            or $this->tg->getAccessToken() === 'false'
        ) {
            return false;
        }

        return true;
    }
}
