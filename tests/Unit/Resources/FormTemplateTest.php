<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\Form\Type\ChannelPlausibleType;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * The help panel next to the identifier field shows the same example as the field's placeholder.
 * Both come from one constant; the template refers to it by name, so a rename would otherwise only
 * surface as an empty example in the admin UI.
 */
final class FormTemplateTest extends TestCase
{
    private const TEMPLATE = __DIR__ . '/../../../src/Resources/views/admin/channel/_form.html.twig';

    /**
     * @test
     */
    public function it_does_not_repeat_the_example_identifier(): void
    {
        self::assertStringNotContainsString(
            ChannelPlausibleType::EXAMPLE_IDENTIFIER,
            self::template(),
            'The template repeats the example instead of referring to the constant',
        );
    }

    /**
     * @test
     */
    public function its_example_identifier_resolves_to_the_form_type_constant(): void
    {
        $matched = preg_match("/constant\\('([^']+)'\\)/", self::template(), $matches);
        self::assertSame(1, $matched, 'The template does not reference a constant for the example');

        // render the expression exactly as it appears in the template
        $twig = new Environment(new ArrayLoader(['t' => "{{ constant('" . $matches[1] . "') }}"]));

        self::assertSame(ChannelPlausibleType::EXAMPLE_IDENTIFIER, $twig->render('t'));
    }

    private static function template(): string
    {
        $contents = file_get_contents(self::TEMPLATE);
        self::assertIsString($contents);

        return $contents;
    }
}
