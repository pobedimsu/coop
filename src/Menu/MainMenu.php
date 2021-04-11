<?php

declare(strict_types=1);

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class MainMenu
{
    private FactoryInterface $factory;
    private KernelInterface $kernel;

    public function __construct(FactoryInterface $factory, KernelInterface $kernel)
    {
        $this->factory = $factory;
        $this->kernel  = $kernel;
    }

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

        $menu->addChild('My deals', ['route' => 'deals'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link py-0')
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
        $menu->addChild('Telegram', ['route' => 'profile_telegram']);
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
        $menu->setExtra('translation_domain', false);

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
                //->setExtra('translation_domain', false)
            ;
        }
    }
}
