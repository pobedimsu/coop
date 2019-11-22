<?php

declare(strict_types=1);

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class MainMenu // implements ContainerAwareInterface
{
    //use ContainerAwareTrait;

    private $factory;

    /**
     * @param FactoryInterface $factory
     *
     * Add any other dependency you need
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Главное меню
     *
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function top(array $options)
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

        $menu->addChild('Offers', ['route' => 'offers'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
        ;

        $menu->addChild('Demand', ['route' => 'demand'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
        ;

        $menu->addChild('Deals', ['route' => 'deals'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
        ;

        $menu->addChild('Joint Purchases', ['route' => 'jp'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
        ;

        $menu->addChild('Users', ['route' => 'users'])
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
     *
     * @param array $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function profile(array $options)
    {
        $menu = $this->factory->createItem('root', [
            'childrenAttributes'    => [
                'class' => 'nav _flex-column nav-pills',
            ],
        ]);

        $menu->addChild('Common', ['route' => 'profile']);
        $menu->addChild('Telegram', ['route' => 'profile_telegram']);
        $menu->addChild('Geoposition', ['route' => 'profile_geoposition']);
        $menu->addChild('Invited users', ['route' => 'profile_invited']);
        $menu->addChild('Change password', ['route' => 'profile_password']);

        return $menu;
    }
}
