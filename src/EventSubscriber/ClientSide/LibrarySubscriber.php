<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber\ClientSide;

use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\Tag\ScriptTag;
use Setono\TagBag\Tag\TagInterface;
use Setono\TagBag\TagBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use function Symfony\Component\String\u;

final class LibrarySubscriber implements EventSubscriberInterface
{
    final public const TAG_FINGERPRINT = 'plausible-library';

    public function __construct(
        private readonly TagBagInterface $tagBag,
        private readonly string $script,
        private readonly ?string $domain,
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
        if (!$event->isMainRequest()) {
            return;
        }

        $this->tagBag->add(
            InlineScriptTag::create('window.plausible = window.plausible || function() { (window.plausible.q = window.plausible.q || []).push(arguments) }')
                ->withPriority(100),
        );

        $domain = $this->domain ?? u($event->getRequest()->getHost())->trimPrefix('www.')->toString();

        $this->tagBag->add(
            ScriptTag::create($this->script)
                ->defer()
                ->withSection(TagInterface::SECTION_HEAD)
                ->withAttribute('data-domain', $domain)
                ->withFingerprint(self::TAG_FINGERPRINT),
        );
    }
}
