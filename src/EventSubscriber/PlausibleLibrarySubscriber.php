<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;
use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\Tag\ScriptTag;
use Setono\TagBag\Tag\TagInterface;
use Setono\TagBag\TagBagInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class PlausibleLibrarySubscriber implements EventSubscriberInterface
{
    final public const TAG_FINGERPRINT = 'plausible-library';

    public function __construct(
        private readonly TagBagInterface $tagBag,
        private readonly ChannelContextInterface $channelContext,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'add',
        ];
    }

    public function add(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || $event->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $accept = $event->getRequest()->headers->get('Accept');
        if (!is_string($accept) || !str_contains($accept, 'html')) {
            return;
        }

        try {
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException) {
            return;
        }

        if (!$channel instanceof ChannelInterface) {
            return;
        }

        $identifier = $channel->getPlausibleScriptIdentifier();
        if (null === $identifier || '' === $identifier) {
            return;
        }

        $this->tagBag->add(
            ScriptTag::create(sprintf('https://plausible.io/js/%s.js', $identifier))
                ->async()
                ->withSection(TagInterface::SECTION_HEAD)
                ->withFingerprint(self::TAG_FINGERPRINT),
        );

        $this->tagBag->add(
            InlineScriptTag::create('window.plausible=window.plausible||function(){(plausible.q=plausible.q||[]).push(arguments)},plausible.init=plausible.init||function(i){plausible.o=i||{}};plausible.init()')
                ->withPriority(99)
                ->withSection(TagInterface::SECTION_HEAD),
        );
    }
}
