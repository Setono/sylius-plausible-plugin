<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber;

use Setono\SyliusPlausiblePlugin\Model\ChannelInterface;
use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\Tag\ScriptTag;
use Setono\TagBag\Tag\TagInterface;
use Setono\TagBag\TagBagInterface;
use Sylius\Bundle\AdminBundle\SectionResolver\AdminSection;
use Sylius\Bundle\CoreBundle\SectionResolver\SectionProviderInterface;
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
        private readonly SectionProviderInterface $sectionProvider,
        private readonly string $scriptHost,
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

        // The admin layout never renders the tag bag, so anything added here would only be
        // written to the admin user's session and carried around until a shop page flushes it
        if ($this->sectionProvider->getSection() instanceof AdminSection) {
            return;
        }

        $accept = $event->getRequest()->headers->get('Accept');
        if (!is_string($accept) || !str_contains($accept, 'html')) {
            return;
        }

        // The init snippet defines plausible() and is added on every shop page. Events can be rendered
        // on any page - the begin checkout event is created on a redirect and rendered on the next
        // one - so the function they call has to exist wherever they end up. Until the library loads,
        // calls are only queued in the browser; nothing is sent to Plausible.
        $this->tagBag->add(
            InlineScriptTag::create('window.plausible=window.plausible||function(){(plausible.q=plausible.q||[]).push(arguments)},plausible.init=plausible.init||function(i){plausible.o=i||{}};plausible.init()')
                ->withPriority(99)
                ->withSection(TagInterface::SECTION_HEAD),
        );

        // The library is what actually sends data, so it is only added for channels where Plausible is set up
        $identifier = $this->resolveScriptIdentifier();
        if (null === $identifier) {
            return;
        }

        $this->tagBag->add(
            ScriptTag::create(sprintf('%s/js/%s.js', $this->scriptHost, $identifier))
                ->async()
                ->withSection(TagInterface::SECTION_HEAD)
                ->withFingerprint(self::TAG_FINGERPRINT),
        );
    }

    private function resolveScriptIdentifier(): ?string
    {
        try {
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException) {
            return null;
        }

        if (!$channel instanceof ChannelInterface) {
            return null;
        }

        $identifier = $channel->getPlausibleScriptIdentifier();

        return null === $identifier || '' === $identifier ? null : $identifier;
    }
}
