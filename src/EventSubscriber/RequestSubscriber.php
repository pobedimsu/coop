<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestSubscriber implements EventSubscriberInterface
{
    protected $router;
    protected $token_storage;
    protected $tgBotName;

    public function __construct(RouterInterface $router, TokenStorageInterface $token_storage, ?string $tgBotName)
    {
        $this->router        = $router;
        $this->token_storage = $token_storage;
        $this->tgBotName     = $tgBotName;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (null === $token = $this->token_storage->getToken()) {
            return;
        }

        /** @var User $user */
        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        // Если указано имя чат-бота, то необходимо привязать тг аккаунт
        if ($this->tgBotName and empty($user->getTelegramUsername())) {
            $requestRoute = $event->getRequest()->get('_route');
            $route = 'profile_telegram';

            if ($route === $requestRoute
                or 'homepage' === $requestRoute
                or 'manual' === $requestRoute
                or 'manual_index' === $requestRoute
                or 'manual_index' === $requestRoute
            ) {
                return;
            }

            $response = new RedirectResponse($this->router->generate($route));
            $event->setResponse($response);
        }
    }
}
