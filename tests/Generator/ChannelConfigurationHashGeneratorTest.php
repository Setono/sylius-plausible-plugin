<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGenerator;
use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;

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

        $repository = $this->prophesize(ChannelRepositoryInterface::class);
        $repository->findAll()->willReturn([$channel1->reveal(), $channel2->reveal()]);

        $generator = new ChannelConfigurationHashGenerator($repository->reveal());

        $hash1 = $generator->generate();
        $hash2 = $generator->generate();

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

        $repository = $this->prophesize(ChannelRepositoryInterface::class);
        $repository->findAll()->willReturn([$channel->reveal()]);

        $generator = new ChannelConfigurationHashGenerator($repository->reveal());
        $hash1 = $generator->generate();

        // Change the configuration
        $channel2 = $this->prophesize(ChannelInterface::class);
        $channel2->getCode()->willReturn('WEB');
        $channel2->getPlausibleScriptIdentifier()->willReturn('pa-different');

        $repository2 = $this->prophesize(ChannelRepositoryInterface::class);
        $repository2->findAll()->willReturn([$channel2->reveal()]);

        $generator2 = new ChannelConfigurationHashGenerator($repository2->reveal());
        $hash2 = $generator2->generate();

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

        $repository1 = $this->prophesize(ChannelRepositoryInterface::class);
        $repository1->findAll()->willReturn([$channel1->reveal(), $channel2->reveal()]);

        $repository2 = $this->prophesize(ChannelRepositoryInterface::class);
        $repository2->findAll()->willReturn([$channel2->reveal(), $channel1->reveal()]);

        $generator1 = new ChannelConfigurationHashGenerator($repository1->reveal());
        $generator2 = new ChannelConfigurationHashGenerator($repository2->reveal());

        self::assertSame($generator1->generate(), $generator2->generate());
    }

    /**
     * @test
     */
    public function it_handles_channels_without_plausible_configured(): void
    {
        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getCode()->willReturn('WEB');
        $channel->getPlausibleScriptIdentifier()->willReturn(null);

        $repository = $this->prophesize(ChannelRepositoryInterface::class);
        $repository->findAll()->willReturn([$channel->reveal()]);

        $generator = new ChannelConfigurationHashGenerator($repository->reveal());

        $hash = $generator->generate();

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

        $repository = $this->prophesize(ChannelRepositoryInterface::class);
        $repository->findAll()->willReturn([$channel->reveal()]);

        $generator = new ChannelConfigurationHashGenerator($repository->reveal());

        $hash = $generator->generate();

        self::assertSame(64, strlen($hash));
    }
}
