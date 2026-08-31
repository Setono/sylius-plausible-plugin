<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGenerator;
use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGenerator
 */
final class ChannelConfigurationHashGeneratorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_generates_consistent_hash_for_same_configuration(): void
    {
        $channel1 = $this->prophesize(ChannelInterface::class);
        $channel1->getCode()->willReturn('WEB');
        $channel1->getPlausibleScriptIdentifier()->willReturn('pa-abc123');

        $channel2 = $this->prophesize(ChannelInterface::class);
        $channel2->getCode()->willReturn('MOBILE');
        $channel2->getPlausibleScriptIdentifier()->willReturn('pa-xyz789');

        $channels = [$channel1->reveal(), $channel2->reveal()];

        $generator = new ChannelConfigurationHashGenerator();

        $hash1 = $generator->generate($channels);
        $hash2 = $generator->generate($channels);

        self::assertSame($hash1, $hash2);
        self::assertSame(64, strlen($hash1)); // SHA256 produces 64 hex characters
    }

    /**
     * @test
     */
    public function it_generates_different_hash_when_configuration_changes(): void
    {
        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getCode()->willReturn('WEB');
        $channel->getPlausibleScriptIdentifier()->willReturn('pa-abc123');

        $channels = [$channel->reveal()];

        $generator = new ChannelConfigurationHashGenerator();
        $hash1 = $generator->generate($channels);

        // Change the configuration
        $channel2 = $this->prophesize(ChannelInterface::class);
        $channel2->getCode()->willReturn('WEB');
        $channel2->getPlausibleScriptIdentifier()->willReturn('pa-different');

        $channelsB = [$channel2->reveal()];

        $generator2 = new ChannelConfigurationHashGenerator();
        $hash2 = $generator2->generate($channelsB);

        self::assertNotSame($hash1, $hash2);
    }

    /**
     * @test
     */
    public function it_generates_same_hash_regardless_of_channel_order(): void
    {
        $channel1 = $this->prophesize(ChannelInterface::class);
        $channel1->getCode()->willReturn('WEB');
        $channel1->getPlausibleScriptIdentifier()->willReturn('pa-abc123');

        $channel2 = $this->prophesize(ChannelInterface::class);
        $channel2->getCode()->willReturn('MOBILE');
        $channel2->getPlausibleScriptIdentifier()->willReturn('pa-xyz789');

        $channelsA = [$channel1->reveal(), $channel2->reveal()];

        $channelsB = [$channel2->reveal(), $channel1->reveal()];

        $generator1 = new ChannelConfigurationHashGenerator();
        $generator2 = new ChannelConfigurationHashGenerator();

        self::assertSame($generator1->generate($channelsA), $generator2->generate($channelsB));
    }

    /**
     * @test
     */
    public function it_handles_channels_without_plausible_configured(): void
    {
        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getCode()->willReturn('WEB');
        $channel->getPlausibleScriptIdentifier()->willReturn(null);

        $channels = [$channel->reveal()];

        $generator = new ChannelConfigurationHashGenerator();

        $hash = $generator->generate($channels);

        self::assertSame(64, strlen($hash));
    }

    /**
     * @test
     */
    public function it_skips_channels_without_code(): void
    {
        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getCode()->willReturn(null);
        $channel->getPlausibleScriptIdentifier()->willReturn('pa-abc123');

        $channels = [$channel->reveal()];

        $generator = new ChannelConfigurationHashGenerator();

        $hash = $generator->generate($channels);

        self::assertSame(64, strlen($hash));
    }
}
