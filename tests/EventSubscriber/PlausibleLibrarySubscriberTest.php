<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPlausiblePlugin\EventSubscriber\PlausibleLibrarySubscriber;
use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;
use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\Tag\ScriptTag;
use Setono\TagBag\TagBagInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\ChannelInterface as CoreChannelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @covers \Setono\SyliusPlausiblePlugin\EventSubscriber\PlausibleLibrarySubscriber
 */
final class PlausibleLibrarySubscriberTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_subscribes_to_kernel_request_event(): void
    {
        self::assertArrayHasKey(KernelEvents::REQUEST, PlausibleLibrarySubscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function it_adds_plausible_script_tags_when_channel_has_identifier(): void
    {
        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getPlausibleScriptIdentifier()->willReturn('pa-test123');

        $channelContext = $this->prophesize(ChannelContextInterface::class);
        $channelContext->getChannel()->willReturn($channel->reveal());

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::that(fn ($tag) => $tag instanceof ScriptTag &&
            null !== $tag->getSrc() &&
            str_contains($tag->getSrc(), 'https://plausible.io/js/pa-test123.js')))->shouldBeCalled();
        $tagBag->add(Argument::that(fn ($tag) => $tag instanceof InlineScriptTag &&
            str_contains($tag->getContent(), 'window.plausible')))->shouldBeCalled();

        $request = new Request();
        $request->headers->set('Accept', 'text/html');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PlausibleLibrarySubscriber($tagBag->reveal(), $channelContext->reveal());
        $subscriber->add($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_add_tags_for_sub_requests(): void
    {
        $channelContext = $this->prophesize(ChannelContextInterface::class);

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->headers->set('Accept', 'text/html');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::SUB_REQUEST);

        $subscriber = new PlausibleLibrarySubscriber($tagBag->reveal(), $channelContext->reveal());
        $subscriber->add($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_add_tags_for_xml_http_requests(): void
    {
        $channelContext = $this->prophesize(ChannelContextInterface::class);

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->headers->set('Accept', 'text/html');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PlausibleLibrarySubscriber($tagBag->reveal(), $channelContext->reveal());
        $subscriber->add($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_add_tags_for_non_html_requests(): void
    {
        $channelContext = $this->prophesize(ChannelContextInterface::class);

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->headers->set('Accept', 'application/json');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PlausibleLibrarySubscriber($tagBag->reveal(), $channelContext->reveal());
        $subscriber->add($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_add_tags_when_channel_not_found(): void
    {
        $channelContext = $this->prophesize(ChannelContextInterface::class);
        $channelContext->getChannel()->willThrow(new ChannelNotFoundException());

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->headers->set('Accept', 'text/html');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PlausibleLibrarySubscriber($tagBag->reveal(), $channelContext->reveal());
        $subscriber->add($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_add_tags_when_channel_does_not_implement_plugin_interface(): void
    {
        $channel = $this->prophesize(CoreChannelInterface::class);

        $channelContext = $this->prophesize(ChannelContextInterface::class);
        $channelContext->getChannel()->willReturn($channel->reveal());

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->headers->set('Accept', 'text/html');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PlausibleLibrarySubscriber($tagBag->reveal(), $channelContext->reveal());
        $subscriber->add($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_add_tags_when_identifier_is_null(): void
    {
        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getPlausibleScriptIdentifier()->willReturn(null);

        $channelContext = $this->prophesize(ChannelContextInterface::class);
        $channelContext->getChannel()->willReturn($channel->reveal());

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->headers->set('Accept', 'text/html');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PlausibleLibrarySubscriber($tagBag->reveal(), $channelContext->reveal());
        $subscriber->add($requestEvent);
    }

    /**
     * @test
     */
    public function it_does_not_add_tags_when_identifier_is_empty_string(): void
    {
        $channel = $this->prophesize(ChannelInterface::class);
        $channel->getPlausibleScriptIdentifier()->willReturn('');

        $channelContext = $this->prophesize(ChannelContextInterface::class);
        $channelContext->getChannel()->willReturn($channel->reveal());

        $tagBag = $this->prophesize(TagBagInterface::class);
        $tagBag->add(Argument::any())->shouldNotBeCalled();

        $request = new Request();
        $request->headers->set('Accept', 'text/html');

        $kernel = $this->prophesize(HttpKernelInterface::class);
        $requestEvent = new RequestEvent($kernel->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber = new PlausibleLibrarySubscriber($tagBag->reveal(), $channelContext->reveal());
        $subscriber->add($requestEvent);
    }
}
