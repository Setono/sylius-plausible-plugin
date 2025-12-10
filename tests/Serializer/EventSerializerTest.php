<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Serializer;

use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use function Setono\SyliusPlausiblePlugin\formatMoney;
use Setono\SyliusPlausiblePlugin\Serializer\EventSerializer;
use Setono\SyliusPlausiblePlugin\Serializer\EventSerializerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Webmozart\Assert\Assert;

final class EventSerializerTest extends KernelTestCase
{
    private EventSerializer $eventSerializer;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $serializer = $container->get('serializer');
        Assert::isInstanceOf($serializer, SerializerInterface::class);

        $this->eventSerializer = new EventSerializer($serializer);
    }

    /**
     * @test
     */
    public function it_serializes1(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setProperty('prop1', 'value1')
            ->setRevenue('USD', formatMoney(1234))
        ;

        self::assertSame(
            '{"props":{"prop1":"value1"},"revenue":{"currency":"USD","amount":12.34}}',
            $this->eventSerializer->serialize($event, EventSerializerInterface::CONTEXT_CLIENT_SIDE),
        );
    }

    /**
     * @test
     */
    public function it_serializes2(): void
    {
        $event = (new Event(Events::PURCHASE))
            ->setRevenue('USD', formatMoney(1234))
        ;

        self::assertSame('{"revenue":{"currency":"USD","amount":12.34}}', $this->eventSerializer->serialize($event, 'client_side'));
    }
}
