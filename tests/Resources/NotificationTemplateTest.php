<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\Controller\DismissNotificationAction;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * The template and the controller have to agree on the CSRF token id. If they drift apart the
 * dismiss button silently stops working - the request is simply rejected - so the template refers
 * to the controller's constant rather than repeating the literal.
 */
final class NotificationTemplateTest extends TestCase
{
    private const TEMPLATE = __DIR__ . '/../../src/Resources/views/admin/_notification.html.twig';

    /**
     * @test
     */
    public function it_does_not_repeat_the_csrf_token_id(): void
    {
        self::assertStringNotContainsString(
            "csrf_token('" . DismissNotificationAction::CSRF_TOKEN_ID . "')",
            self::template(),
            'The template repeats the token id instead of referring to the constant',
        );
    }

    /**
     * @test
     */
    public function its_csrf_token_id_resolves_to_the_controller_constant(): void
    {
        $matched = preg_match("/constant\\('([^']+)'\\)/", self::template(), $matches);
        self::assertSame(1, $matched, 'The template does not reference a constant for the token id');

        // render the expression exactly as it appears in the template
        $twig = new Environment(new ArrayLoader(['t' => "{{ constant('" . $matches[1] . "') }}"]));

        self::assertSame(DismissNotificationAction::CSRF_TOKEN_ID, $twig->render('t'));
    }

    private static function template(): string
    {
        $contents = file_get_contents(self::TEMPLATE);
        self::assertIsString($contents);

        return $contents;
    }
}
