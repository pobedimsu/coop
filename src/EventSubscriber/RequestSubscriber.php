<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\TelegramService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ?string $tgBotName,
        private RequestStack $requestStack,
        private RouterInterface $router,
        private TokenStorageInterface $tokenStorage,
        private TelegramService $telegram,
    ) {}

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
        if ($this->telegram->isEnable() and $this->tgBotName and empty($user->getTelegramUsername())) {
            $requestRoute = $event->getRequest()->get('_route');
            $route = 'profile_telegram';

            if ($route === $requestRoute
                or 'homepage' === $requestRoute
                or 'manual' === $requestRoute
                or 'manual_index' === $requestRoute
            ) {
                return;
            }

            $this->requestStack->getMainRequest()->getSession()->getFlashBag()->add('notice', 'Для доступа ко всем функциям сайта, нужно подключить свой аккаунт Телеграм.');

            $response = new RedirectResponse($this->router->generate($route));
            $event->setResponse($response);
        }
    }
}
