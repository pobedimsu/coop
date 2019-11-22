<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Borsaco\TelegramBotApiBundle\Service\Bot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Traits\Telegram;

class TelegramController extends AbstractController
{
    /**
     * @param Request                $request
     * @param Bot                    $bot
     * @param EntityManagerInterface $em
     * @param KernelInterface        $kernel - не искользуется
     *
     * @return Response
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     *
     * @Route("/telegram/", name="telegram")
     */
    public function index(Request $request, Bot $bot, EntityManagerInterface $em, KernelInterface $kernel): Response
    {
        if ($request->getMethod() == 'POST') {
            $tg = $bot->getBot();
            $telegram = $tg; // temp

            try {
                $result = $tg->getWebhookUpdate();

                $text    = $result['message']['text']; //Текст сообщения
                $chat_id = $result['message']['chat']['id']; //Уникальный идентификатор пользователя

                if (isset($result['message']['from']['username'])) {
                    $username = $result['message']['from']['username']; //Юзернейм пользователя
                } else {
                    $username = null;
                }
            } catch (\ErrorException $e) {
                return new Response($e->getMessage());
            }

            //$user = $em->getRepository(User::class)->findOneBy(['id']);

            if($text) {
                if ($text == '/start') {
                    $reply = 'Добро пожаловать!';

                    if (empty($username)) {
                        $reply .= "\n\nУ вас не задано имя пользователя. Пожалуйста, укажите его в настройках телеграма";
                    }

                    $keyboard = Keyboard::make()
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(false)
                        ->row(
                            Keyboard::button(['text' => 'Баланс']),
                            Keyboard::button(['text' => 'Предложения']),
                            Keyboard::button(['text' => 'Сделки']),
                        //                        Keyboard::inlineButton(['text' => 'Test', 'url' => 'https://www.google.com']),
                        );
                    $keyboard = Keyboard::remove();

                    $telegram->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $reply,
                        'parse_mode' => 'html',
                        //'reply_markup' => $keyboard,
                    ]);
                } elseif ($text == '/help') {
                    $reply = 'Информация с помощью.';
                    $telegram->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply]);
                } elseif ($text == 'Баланс') {
                    $tg->sendMessage(['chat_id' => $chat_id, 'text' => "Ваш баланс \xF0\x9F\x92\xB0 : 0 "]);
                } elseif (is_numeric($text)) {
                    if (empty($username)) {
                        $reply = "\n\nУ вас не задано имя пользователя. Пожалуйста, укажите его в настройках телеграма";
                    } else {
                        $cache = new FilesystemAdapter();
                        $user_id = $cache->getItem('connect_telegram_account_code'.$text)->get();

                        if (empty($user_id)) {
                            $reply = 'Код не действителен.';
                        } else {
                            $user = $em->getRepository(User::class)->findOneBy(['id' => $user_id]);

                            if (empty($user)) {
                                $reply = 'Код не действителен..';
                            } else {
                                $user_uniquie = $em->getRepository(User::class)->findOneBy(['telegram_user_id' => $chat_id]);

                                if (empty($user_uniquie)) { // ok
                                    $user->setTelegramUserId($chat_id);
                                    $user->setTelegramUsername($username);
                                    // @todo обработка ошибки при уникальности
                                    $em->flush();
                                    $reply = 'ok';

                                    $cache->delete('connect_telegram_account_code'.$text);
                                } else {
                                    $reply = 'Юзер занят';
                                }
                            }
                        }
                    }

                    $tg->sendMessage([ 'chat_id' => $chat_id, 'text' => $reply]);
                } else {
                    $tg->sendMessage([ 'chat_id' => $chat_id, 'text' => 'bad command']);
                }
            } else {
                $tg->sendMessage(['chat_id' => $chat_id, 'text' => 'Отправьте текстовое сообщение']);
            }

//            ob_start();
//            var_dump($result);
//
//            file_put_contents($kernel->getLogDir().'/tg.log', ob_get_clean());
        }

        return new Response('<html><body></body></html>');
    }
}
