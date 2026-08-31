<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Checker;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusPlausiblePlugin\Checker\NotificationChecker;
use Setono\SyliusPlausiblePlugin\Generator\ChannelConfigurationHashGeneratorInterface;
use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;
use Setono\SyliusPlausiblePlugin\Model\NotificationDismissalInterface;
use Setono\SyliusPlausiblePlugin\Repository\NotificationDismissalRepositoryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @covers \Setono\SyliusPlausiblePlugin\Checker\NotificationChecker
 */
final class NotificationCheckerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<NotificationDismissalRepositoryInterface> */
    private ObjectProphecy $dismissalRepository;

    /** @var ObjectProphecy<Security> */
    private ObjectProphecy $security;

    /** @var ObjectProphecy<ChannelConfigurationHashGeneratorInterface> */
    private ObjectProphecy $hashGenerator;

    /** @var ObjectProphecy<ChannelRepositoryInterface<ChannelInterface>> */
    private ObjectProphecy $channelRepository;

    private ?AdminUserInterface $adminUser = null;

    protected function setUp(): void
    {
        $this->dismissalRepository = $this->prophesize(NotificationDismissalRepositoryInterface::class);
        $this->security = $this->prophesize(Security::class);
        $this->hashGenerator = $this->prophesize(ChannelConfigurationHashGeneratorInterface::class);
        /** @var ObjectProphecy<ChannelRepositoryInterface<ChannelInterface>> $channelRepository */
        $channelRepository = $this->prophesize(ChannelRepositoryInterface::class);
        $this->channelRepository = $channelRepository;
        $this->adminUser = null;
    }

    /**
     * @test
     */
    public function it_returns_false_when_user_is_not_admin(): void
    {
        $this->security->getUser()->willReturn(null);

        self::assertFalse($this->createChecker()->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_returns_false_when_all_channels_are_configured(): void
    {
        $this->loginAsAdmin();
        $this->channelRepository->findAll()->willReturn([
            $this->channel('pa-one'),
            $this->channel('pa-two'),
        ]);

        self::assertFalse($this->createChecker()->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_returns_false_when_there_are_no_channels(): void
    {
        $this->loginAsAdmin();
        $this->channelRepository->findAll()->willReturn([]);

        self::assertFalse($this->createChecker()->shouldShowNotification());
    }

    /**
     * A disabled channel doesn't serve any traffic, so it can't be missing tracking. Leaving it
     * out of the check stops a legacy channel from pinning the notification open forever.
     *
     * @test
     */
    public function it_ignores_disabled_channels(): void
    {
        $this->loginAsAdmin();
        $this->channelRepository->findAll()->willReturn([
            $this->channel('pa-one'),
            $this->channel(null, enabled: false),
        ]);

        self::assertFalse($this->createChecker()->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_returns_true_when_an_enabled_channel_has_no_identifier(): void
    {
        $this->loginAsAdmin();
        $this->channelRepository->findAll()->willReturn([
            $this->channel('pa-one'),
            $this->channel(null),
        ]);
        $this->hashGenerator->generate(Argument::type('array'))->willReturn('hash123');
        $this->dismissalRepository->findValidDismissal($this->adminUser(), 'hash123')->willReturn(null);

        self::assertTrue($this->createChecker()->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_returns_true_when_an_enabled_channel_has_an_empty_identifier(): void
    {
        $this->loginAsAdmin();
        $this->channelRepository->findAll()->willReturn([$this->channel('')]);
        $this->hashGenerator->generate(Argument::type('array'))->willReturn('hash123');
        $this->dismissalRepository->findValidDismissal($this->adminUser(), 'hash123')->willReturn(null);

        self::assertTrue($this->createChecker()->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_returns_false_when_user_has_a_dismissal_for_the_current_configuration(): void
    {
        $this->loginAsAdmin();
        $this->channelRepository->findAll()->willReturn([$this->channel(null)]);
        $this->hashGenerator->generate(Argument::type('array'))->willReturn('hash123');

        $dismissal = $this->prophesize(NotificationDismissalInterface::class);
        $this->dismissalRepository->findValidDismissal($this->adminUser(), 'hash123')->willReturn($dismissal->reveal());

        self::assertFalse($this->createChecker()->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_returns_true_when_the_dismissal_is_for_an_older_configuration(): void
    {
        $this->loginAsAdmin();
        $this->channelRepository->findAll()->willReturn([$this->channel(null)]);
        $this->hashGenerator->generate(Argument::type('array'))->willReturn('new-hash');
        $this->dismissalRepository->findValidDismissal($this->adminUser(), 'new-hash')->willReturn(null);

        self::assertTrue($this->createChecker()->shouldShowNotification());
    }

    /**
     * Both the configured check and the hash are derived from the same channels, so the
     * repository must only be hit once per call.
     *
     * @test
     */
    public function it_loads_the_channels_once(): void
    {
        $this->loginAsAdmin();

        $channels = [$this->channel('pa-one'), $this->channel(null)];
        $this->channelRepository->findAll()->willReturn($channels)->shouldBeCalledOnce();

        $this->hashGenerator->generate($channels)->willReturn('hash123')->shouldBeCalledOnce();
        $this->dismissalRepository->findValidDismissal($this->adminUser(), 'hash123')->willReturn(null);

        self::assertTrue($this->createChecker()->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_does_not_generate_a_hash_when_every_channel_is_configured(): void
    {
        $this->loginAsAdmin();
        $this->channelRepository->findAll()->willReturn([$this->channel('pa-one')])->shouldBeCalledOnce();

        $this->hashGenerator->generate(Argument::any())->shouldNotBeCalled();
        $this->dismissalRepository->findValidDismissal(Argument::cetera())->shouldNotBeCalled();

        self::assertFalse($this->createChecker()->shouldShowNotification());
    }

    /**
     * @test
     */
    public function it_touches_no_repository_when_there_is_no_admin_user(): void
    {
        $this->security->getUser()->willReturn(null);

        $this->channelRepository->findAll()->shouldNotBeCalled();
        $this->hashGenerator->generate(Argument::any())->shouldNotBeCalled();

        self::assertFalse($this->createChecker()->shouldShowNotification());
    }

    private function createChecker(): NotificationChecker
    {
        return new NotificationChecker(
            $this->dismissalRepository->reveal(),
            $this->security->reveal(),
            $this->hashGenerator->reveal(),
            $this->channelRepository->reveal(),
        );
    }

    private function channel(?string $identifier, bool $enabled = true): ChannelInterface
    {
        $channel = $this->prophesize(ChannelInterface::class);
        $channel->isEnabled()->willReturn($enabled);
        $channel->getPlausibleScriptIdentifier()->willReturn($identifier);

        return $channel->reveal();
    }

    private function adminUser(): AdminUserInterface
    {
        return $this->adminUser ??= $this->prophesize(AdminUserInterface::class)->reveal();
    }

    private function loginAsAdmin(): void
    {
        $this->security->getUser()->willReturn($this->adminUser());
    }
}
