<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\EventSubscriber\ClientSide;

use Setono\TagBag\Tag\InlineScriptTag;
use Setono\TagBag\Tag\ScriptTag;
use Setono\TagBag\Tag\TagInterface;
use Setono\TagBag\TagBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

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
            KernelEvents::RESPONSE => 'add',
        ];
    }

    public function add(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        // This 'if' is copied from \Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener
        if ($response->isRedirection() ||
            $request->isXmlHttpRequest() ||
            'html' !== $request->getRequestFormat() ||
            str_contains($response->headers->get('Content-Disposition') ?? '', 'attachment;') ||
            ($response->headers->has('Content-Type') && !str_contains($response->headers->get('Content-Type') ?? '', 'html'))
        ) {
            return;
        }

        $this->tagBag->add(
            InlineScriptTag::create('window.plausible = window.plausible || function() { (window.plausible.q = window.plausible.q || []).push(arguments) }')
                ->withPriority(100),
        );

        $this->tagBag->add(
            ScriptTag::create($this->script)
                ->defer()
                ->withSection(TagInterface::SECTION_HEAD)
                ->withAttribute('data-domain', $this->domain ?? $event->getRequest()->getHost())
                ->withFingerprint(self::TAG_FINGERPRINT),
        );
    }
}
