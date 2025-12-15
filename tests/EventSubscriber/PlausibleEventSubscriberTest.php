<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\EventSubscriber;

use Psr\Log\AbstractLogger;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Setono\SyliusPlausiblePlugin\EventSubscriber\PlausibleEventSubscriber;
use function Setono\SyliusPlausiblePlugin\formatMoney;
use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\Tag\TagInterface;
use Setono\TagBag\TagBagInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\PlausibleEventSubscriber
 */
final class PlausibleEventSubscriberTest extends KernelTestCase
{
    private PlausibleEventSubscriber $subscriber;

    private TestTagBag $tagBag;

    private TestLogger $logger;

    protected function setUp(): void
    {
        self::bootKernel();

        $serializer = self::getContainer()->get('serializer');
        Assert::isInstanceOf($serializer, SerializerInterface::class);

        $this->tagBag = new TestTagBag();
        $this->logger = new TestLogger();
        $this->subscriber = new PlausibleEventSubscriber($serializer, $this->tagBag);
        $this->subscriber->setLogger($this->logger);
    }

    /**
     * @test
     */
    public function it_subscribes_to_plausible_event_with_low_priority(): void
    {
        $subscribedEvents = PlausibleEventSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(Event::class, $subscribedEvents);
        self::assertSame(['add', -100], $subscribedEvents[Event::class]);
    }

    /**
     * @test
     */
    public function it_adds_inline_script_tag_with_properties(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setProperty('order_id', 123)
            ->setProperty('order_total', 99.99);

        $this->subscriber->add($event);

        $tag = $this->getTagOrFail();
        self::assertSame('plausible("Purchase", {"props":{"order_id":123,"order_total":99.99}});', $tag->getContent());
    }

    /**
     * @test
     */
    public function it_adds_inline_script_tag_with_properties_and_revenue(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setProperty('order_id', 456)
            ->setRevenue('USD', formatMoney(1234));

        $this->subscriber->add($event);

        $tag = $this->getTagOrFail();
        self::assertSame('plausible("Purchase", {"props":{"order_id":456},"revenue":{"currency":"USD","amount":12.34}});', $tag->getContent());
    }

    /**
     * @test
     */
    public function it_adds_inline_script_tag_with_revenue_only(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setRevenue('EUR', 50.0);

        $this->subscriber->add($event);

        $tag = $this->getTagOrFail();
        self::assertSame('plausible("Purchase", {"revenue":{"currency":"EUR","amount":50}});', $tag->getContent());
    }

    /**
     * @test
     */
    public function it_adds_inline_script_tag_without_properties(): void
    {
        $event = new Event(Events::BEGIN_CHECKOUT);

        $this->subscriber->add($event);

        $tag = $this->getTagOrFail();
        self::assertSame('plausible("Begin Checkout");', $tag->getContent());
    }

    /**
     * @test
     */
    public function it_excludes_null_property_values(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setProperty('order_id', 123)
            ->setProperty('coupon_code', null);

        $this->subscriber->add($event);

        $tag = $this->getTagOrFail();
        self::assertStringContainsString('"order_id":123', $tag->getContent());
        self::assertStringNotContainsString('coupon_code', $tag->getContent());
    }

    /**
     * @test
     */
    public function it_excludes_empty_string_property_values(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setProperty('order_id', 123)
            ->setProperty('coupon_code', '');

        $this->subscriber->add($event);

        $tag = $this->getTagOrFail();
        self::assertStringContainsString('"order_id":123', $tag->getContent());
        self::assertStringNotContainsString('coupon_code', $tag->getContent());
    }

    private function getTagOrFail(): InlineScriptTag
    {
        $tag = $this->tagBag->getLastTag();
        if (null === $tag) {
            $error = $this->logger->getLastError();
            self::fail(sprintf('No tag was added to the tag bag. Logger error: %s', $error ?? 'none'));
        }

        self::assertInstanceOf(InlineScriptTag::class, $tag);

        return $tag;
    }
}

/**
 * @internal
 */
final class TestTagBag implements TagBagInterface
{
    private ?TagInterface $lastTag = null;

    public function add(TagInterface $tag): void
    {
        $this->lastTag = $tag;
    }

    public function getLastTag(): ?TagInterface
    {
        return $this->lastTag;
    }

    public function renderAll(): string
    {
        return '';
    }

    public function renderSection(string $section): string
    {
        return '';
    }

    public function store(): void
    {
    }

    public function restore(): void
    {
    }
}

/**
 * @internal
 */
final class TestLogger extends AbstractLogger
{
    private ?string $lastError = null;

    /**
     * @param array<array-key, mixed> $context
     */
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        if ('error' === $level) {
            $exception = $context['exception'] ?? null;
            $exceptionMessage = $exception instanceof \Throwable ? ': ' . $exception->getMessage() : '';
            $this->lastError = $message . $exceptionMessage;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
