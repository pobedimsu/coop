<?php

declare(strict_types=1);

namespace App\Menu;

use App\Entity\Deal;
use App\Service\TelegramService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MainMenu
{
    public function __construct(
        private FactoryInterface $factory,
        private EntityManagerInterface $em,
        private KernelInterface $kernel,
        private string $tgBotToken,
        private TelegramService $telegram,
        private TokenStorageInterface $tokenStorage,
    ) {}

    /**
     * Главное меню
     */
    public function top(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root', [
            'childrenAttributes'    => [
                'class' => 'navbar-nav',
            ],
        ]);

        $menu->addChild('Homepage', ['route' => 'homepage'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
        ;

        $menu->addChild('Advertisements', ['route' => 'offers'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
        ;

        $menu->addChild('Demand', ['route' => 'demand'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
        ;

        if ($this->tokenStorage->getToken()) {
            $countNewIncoming = $this->em->getRepository(Deal::class)->countNewIncomingByUser($this->tokenStorage->getToken()->getUser());
            $countActive = $this->em->getRepository(Deal::class)->countActiveByUser($this->tokenStorage->getToken()->getUser());
        } else {
            $countNewIncoming = null;
            $countActive = null;
        }

        $menu->addChild('My deals', ['route' => 'deals', 'routeParameters' => ['tab' => $countNewIncoming ? 'in' : ($countActive ? 'active' : null) ]])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
            ->setExtra('countNewIncoming', $countNewIncoming)
            ->setExtra('countActive', $countActive)
        ;

        $menu->addChild('Joint Purchases', ['route' => 'jp'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
        ;

        $menu->addChild('Manual', ['route' => 'manual_index'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
        ;

        $menu->addChild('Invite', ['route' => 'invite'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
        ;

        return $menu;
    }

    /**
     * Профиль пользователя
     */
    public function profile(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root', [
            'childrenAttributes'    => [
                'class' => 'nav _flex-column nav-pills',
            ],
        ]);

        $menu->setExtra('select_intehitance', false);

        $menu->addChild('Common', ['route' => 'profile']);

        if ($this->telegram->isEnable()) {
            $menu->addChild('Telegram', ['route' => 'profile_telegram']);
        }

        $menu->addChild('Geoposition', ['route' => 'profile_geoposition']);
        $menu->addChild('Invited users', ['route' => 'profile_invited']);
        $menu->addChild('Change password', ['route' => 'profile_password']);

        return $menu;
    }

    /**
     * Построение полной структуры, включая ноды.
     */
    public function manual(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('manual_pages', [
            'childrenAttributes'    => [
                'class' => 'nav flex-column nav-pills',
            ],
        ]);
        $menu
            ->setExtra('translation_domain', false)
            ->setExtra('select_intehitance', false)
        ;

        $this->addManualChild($menu);

        return $menu;
    }

    /**
     * Рекурсивное построение дерева пунктов меню руководства
     */
    protected function addManualChild(ItemInterface $menu, string $path = null): void
    {
        $manDir = $this->kernel->getProjectDir() . '/doc/manual';

        $finder = new Finder();
        $finder->files()->in($manDir)->sortByName();

        $menu->addChild('Description', ['route' => 'manual'])
            ->setLabel('Description')
        ;

        foreach ($finder as $file) {
            $file->getRelativePathname();

            $file = $file->getRelativePathname();

            if ($file === 'README.md') {
                continue;
            }

            $label = mb_substr($file, 0, -3); // Обрезание последних 3-х символов (.md)
            $label = mb_substr($label, 3); // Обрезание первых 3-х символов, нумерация и тире
            $label = str_replace('-', ' ', $label);

            $menu->addChild($file, ['route' => 'manual', 'routeParameters' => ['slug' => $file]])
                ->setLabel($label)
            ;
        }
    }
}
