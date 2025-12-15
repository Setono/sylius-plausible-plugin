<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\EventSubscriber\ClientSide;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Setono\SyliusPlausiblePlugin\EventSubscriber\ClientSide\EventSubscriber;
use Setono\SyliusPlausiblePlugin\Serializer\EventSerializerInterface;
use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\TagBagInterface;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\ClientSide\EventSubscriber
 */
final class EventSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_subscribes_to_plausible_event_with_low_priority(): void
    {
        $subscribedEvents = EventSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(Event::class, $subscribedEvents);
        self::assertSame(['add', -100], $subscribedEvents[Event::class]);
    }

    /**
     * @test
     */
    public function it_adds_inline_script_tag_with_properties(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setProperty('order_id', 123);

        $serializer = $this->prophesize(EventSerializerInterface::class);
        $serializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE)
            ->willReturn('{"props":{"order_id":123}}');

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::that(function ($tag) {
            if (!$tag instanceof InlineScriptTag) {
                return false;
            }

            $content = $tag->getContent();

            return str_contains($content, 'plausible("Purchase"') &&
                str_contains($content, '{"props":{"order_id":123}}');
        }))->shouldBeCalled();

        $subscriber = new EventSubscriber($serializer->reveal(), $tagBag->reveal());
        $subscriber->add($event);
    }

    /**
     * @test
     */
    public function it_adds_inline_script_tag_without_properties(): void
    {
        $event = new Event(Events::BEGIN_CHECKOUT);

        $serializer = $this->prophesize(EventSerializerInterface::class);
        $serializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE)
            ->willReturn('{}');

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::that(function ($tag) {
            if (!$tag instanceof InlineScriptTag) {
                return false;
            }

            $content = $tag->getContent();

            return $content === 'plausible("Begin Checkout");';
        }))->shouldBeCalled();

        $subscriber = new EventSubscriber($serializer->reveal(), $tagBag->reveal());
        $subscriber->add($event);
    }

    /**
     * @test
     */
    public function it_logs_error_when_serialization_fails(): void
    {
        $event = new Event(Events::PURCHASE);

        $serializer = $this->prophesize(EventSerializerInterface::class);
        $serializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE)
            ->willThrow(new \RuntimeException('Serialization error'));

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::any())->shouldNotBeCalled();

        $logger = $this->prophesize(\Psr\Log\LoggerInterface::class);
        $logger->error('Could not encode event to json', Argument::type('array'))->shouldBeCalled();

        $subscriber = new EventSubscriber($serializer->reveal(), $tagBag->reveal());
        $subscriber->setLogger($logger->reveal());
        $subscriber->add($event);
    }
}
