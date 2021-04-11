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

    public function __construct(Bot $bot)
    {
        $this->tg = $bot->getBot();
    }

    public function sendMessage(User $user, string $text): void
    {
        if (empty($user->getTelegramUserId())) {
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
}
