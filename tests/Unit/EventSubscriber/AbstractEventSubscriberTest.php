<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Unit\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Setono\SyliusPlausiblePlugin\EventSubscriber\AbstractEventSubscriber;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\AbstractEventSubscriber
 */
final class AbstractEventSubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_logs_the_event_name_and_the_exception(): void
    {
        $exception = new \RuntimeException('Something broke');

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->error(
            'An error occurred trying to track the event Test Event: Something broke',
            ['exception' => $exception],
        )->shouldBeCalledOnce();

        $subscriber = $this->createSubscriber();
        $subscriber->setLogger($logger->reveal());
        $subscriber->fail($exception);
    }

    /**
     * Logging is optional: the logger is injected by a setter call, so without one the error is discarded.
     *
     * @test
     */
    public function it_does_not_require_a_logger(): void
    {
        $this->expectNotToPerformAssertions();

        $this->createSubscriber()->fail(new \RuntimeException('Something broke'));
    }

    private function createSubscriber(): TestEventSubscriber
    {
        return new TestEventSubscriber($this->prophesize(EventDispatcherInterface::class)->reveal());
    }
}

/**
 * @internal
 */
final class TestEventSubscriber extends AbstractEventSubscriber
{
    public static function getSubscribedEvents(): array
    {
        return [];
    }

    public function fail(\Throwable $e): void
    {
        $this->log('Test Event', $e);
    }
}
