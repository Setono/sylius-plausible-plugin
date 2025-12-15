<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\EventSubscriber;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPlausiblePlugin\EventSubscriber\AdminMenuSubscriber;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\AdminMenuSubscriber
 */
final class AdminMenuSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_subscribes_to_admin_main_menu_event(): void
    {
        self::assertArrayHasKey('sylius.menu.admin.main', AdminMenuSubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function it_adds_plausible_menu_item_to_marketing_section(): void
    {
        $plausibleMenuItem = $this->prophesize(ItemInterface::class);
        $plausibleMenuItem->setLabel('setono_sylius_plausible.ui.plausible')->willReturn($plausibleMenuItem)->shouldBeCalled();
        $plausibleMenuItem->setLabelAttribute('icon', 'chart line')->willReturn($plausibleMenuItem)->shouldBeCalled();

        $marketingMenu = $this->prophesize(ItemInterface::class);
        $marketingMenu->addChild('plausible', [
            'route' => 'setono_sylius_plausible_admin_channel_index',
        ])->willReturn($plausibleMenuItem->reveal())->shouldBeCalled();

        $menu = $this->prophesize(ItemInterface::class);
        $menu->getChild('marketing')->willReturn($marketingMenu->reveal());

        $factory = $this->prophesize(FactoryInterface::class);

        $event = new MenuBuilderEvent($factory->reveal(), $menu->reveal());

        $subscriber = new AdminMenuSubscriber();
        $subscriber->addMenuItems($event);
    }

    /**
     * @test
     */
    public function it_does_nothing_when_marketing_section_does_not_exist(): void
    {
        $menu = $this->prophesize(ItemInterface::class);
        $menu->getChild('marketing')->willReturn(null);

        $factory = $this->prophesize(FactoryInterface::class);

        $event = new MenuBuilderEvent($factory->reveal(), $menu->reveal());

        $subscriber = new AdminMenuSubscriber();
        $subscriber->addMenuItems($event);

        // No exception should be thrown, and we simply return early
        // If we reach this point, the test passes
        $this->expectNotToPerformAssertions();
    }
}
