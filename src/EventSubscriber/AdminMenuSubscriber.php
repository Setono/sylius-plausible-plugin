<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AdminMenuSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.menu.admin.main' => 'addMenuItems',
        ];
    }

    public function addMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $marketing = $menu->getChild('marketing');
        if (null === $marketing) {
            return;
        }

        $marketing
            ->addChild('plausible', [
                'route' => 'setono_sylius_plausible_admin_channel_index',
            ])
            ->setLabel('setono_sylius_plausible.ui.plausible')
            ->setLabelAttribute('icon', 'chart line')
        ;
    }
}
