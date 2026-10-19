<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\Model\ChannelTrait;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Model\ChannelTrait
 */
final class ChannelTraitTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_no_script_identifier_by_default(): void
    {
        self::assertNull((new TestChannel())->getPlausibleScriptIdentifier());
    }

    /**
     * @test
     */
    public function it_sets_and_clears_the_script_identifier(): void
    {
        $channel = new TestChannel();

        $channel->setPlausibleScriptIdentifier('pa-test');
        self::assertSame('pa-test', $channel->getPlausibleScriptIdentifier());

        $channel->setPlausibleScriptIdentifier(null);
        self::assertNull($channel->getPlausibleScriptIdentifier());
    }
}

/**
 * @internal
 */
final class TestChannel
{
    use ChannelTrait;
}
