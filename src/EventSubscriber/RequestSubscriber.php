<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestSubscriber implements EventSubscriberInterface
{
    protected RequestStack $requestStack;
    protected RouterInterface $router;
    protected TokenStorageInterface $tokenStorage;
    protected ?string $tgBotName;

    public function __construct(?string $tgBotName, RequestStack $requestStack, RouterInterface $router, TokenStorageInterface $tokenStorage)
    {
        $this->requestStack = $requestStack;
        $this->router       = $router;
        $this->tokenStorage = $tokenStorage;
        $this->tgBotName    = $tgBotName;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 0],
            ],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        /** @var User $user */
        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
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
            ) {
                return;
            }

            $this->requestStack->getMasterRequest()->getSession()->getFlashBag()->add('notice', 'Для доступа ко всем функциям сайта, нужно подключить свой аккаунт Телеграм.');

            $response = new RedirectResponse($this->router->generate($route));
            $event->setResponse($response);
        }
    }
}
