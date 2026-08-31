<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Integration;

use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Setono\SyliusPlausiblePlugin\EventSubscriber\PlausibleEventSubscriber;
use Setono\SyliusPlausiblePlugin\EventSubscriber\PopulateOrderRelatedPropertiesSubscriber;
use Setono\TagBag\Tag\TagInterface;
use Setono\TagBag\TagBagInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Webmozart\Assert\Assert;

/**
 * Exercises the real container wiring: dispatching the plugin's event has to end up as a rendered
 * inline script. The unit tests all construct their subscribers by hand, so nothing else would
 * notice a missing service definition, a wrong tag, or a broken serialization mapping.
 */
final class TrackingTest extends KernelTestCase
{
    private EventDispatcherInterface $eventDispatcher;

    private TagBagInterface $tagBag;

    protected function setUp(): void
    {
        self::bootKernel();

        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        Assert::isInstanceOf($eventDispatcher, EventDispatcherInterface::class);
        $this->eventDispatcher = $eventDispatcher;

        $tagBag = self::getContainer()->get('setono_tag_bag.tag_bag');
        Assert::isInstanceOf($tagBag, TagBagInterface::class);
        $this->tagBag = $tagBag;
    }

    /**
     * @test
     */
    public function it_renders_a_dispatched_event_as_an_inline_script(): void
    {
        $this->eventDispatcher->dispatch(
            (new Event('Integration Test'))->setProperty('source', 'test suite'),
        );

        self::assertSame(
            '<script>plausible("Integration Test", {"props":{"source":"test suite"}});</script>',
            $this->tagBag->renderSection(TagInterface::SECTION_BODY_END),
        );
    }

    /**
     * @test
     */
    public function it_renders_an_event_without_properties(): void
    {
        $this->eventDispatcher->dispatch(new Event(Events::BEGIN_CHECKOUT));

        self::assertSame(
            '<script>plausible("Begin Checkout");</script>',
            $this->tagBag->renderSection(TagInterface::SECTION_BODY_END),
        );
    }

    /**
     * @test
     */
    public function it_renders_revenue_on_the_purchase_event(): void
    {
        $this->eventDispatcher->dispatch(
            (new Event(Events::PURCHASE))->setRevenue('DKK', 249.95),
        );

        self::assertSame(
            '<script>plausible("Purchase", {"revenue":{"currency":"DKK","amount":249.95}});</script>',
            $this->tagBag->renderSection(TagInterface::SECTION_BODY_END),
        );
    }

    /**
     * The subscriber that enriches the event has to run before the one that renders it, otherwise
     * every event would be tracked without its order properties.
     *
     * @test
     */
    public function it_populates_the_event_before_rendering_it(): void
    {
        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        Assert::isInstanceOf($eventDispatcher, SymfonyEventDispatcherInterface::class);

        $listeners = $eventDispatcher->getListeners(Event::class);

        $order = [];
        foreach ($listeners as $listener) {
            if (is_array($listener) && is_object($listener[0])) {
                $order[] = $listener[0]::class;
            }
        }

        $populateIndex = array_search(PopulateOrderRelatedPropertiesSubscriber::class, $order, true);
        $renderIndex = array_search(PlausibleEventSubscriber::class, $order, true);

        self::assertIsInt($populateIndex, 'The populate subscriber is not registered');
        self::assertIsInt($renderIndex, 'The rendering subscriber is not registered');
        self::assertLessThan($renderIndex, $populateIndex);
    }
}
