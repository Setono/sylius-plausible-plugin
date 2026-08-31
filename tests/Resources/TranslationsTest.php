<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * The plugin ships ten locales. A key that exists in one but not the others, or a translation that
 * drops a placeholder token, only shows up in the admin UI of whoever runs that locale.
 */
final class TranslationsTest extends TestCase
{
    private const TRANSLATIONS_DIR = __DIR__ . '/../../src/Resources/translations';

    /**
     * @test
     *
     * @dataProvider domains
     */
    public function it_has_the_same_keys_in_every_locale(string $domain): void
    {
        /** @var list<string>|null $reference */
        $reference = null;
        $referenceLocale = null;

        foreach (self::localesFor($domain) as $locale => $translations) {
            $keys = array_keys($translations);

            if (null === $reference) {
                $reference = $keys;
                $referenceLocale = $locale;

                continue;
            }

            self::assertSame(
                $reference,
                $keys,
                sprintf('The "%s" translations differ from "%s" in the %s domain', $locale, (string) $referenceLocale, $domain),
            );
        }

        self::assertNotNull($reference, sprintf('No translations found for the %s domain', $domain));
    }

    /**
     * The example identifier is passed in as a translation parameter, so every locale has to keep
     * the token - otherwise that locale silently shows a placeholder without the example.
     *
     * @test
     */
    public function every_locale_keeps_the_identifier_placeholder_token(): void
    {
        $key = 'setono_sylius_plausible.form.channel.plausible_script_identifier_placeholder';

        foreach (self::localesFor('messages') as $locale => $keys) {
            self::assertArrayHasKey($key, $keys, sprintf('Locale "%s" is missing the placeholder', $locale));

            $placeholder = $keys[$key];
            self::assertIsString($placeholder);
            self::assertStringContainsString(
                '%identifier%',
                $placeholder,
                sprintf('The "%s" placeholder dropped the %%identifier%% token', $locale),
            );
        }
    }

    /**
     * @return iterable<array-key, array{string}>
     */
    public static function domains(): iterable
    {
        yield ['messages'];
        yield ['validators'];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function localesFor(string $domain): array
    {
        $result = [];

        foreach ((array) glob(self::TRANSLATIONS_DIR . '/' . $domain . '.*.yaml') as $file) {
            $file = (string) $file;
            preg_match('/\.([a-z]{2})\.yaml$/', $file, $matches);

            /** @var array<string, mixed> $parsed */
            $parsed = Yaml::parseFile($file);
            $flattened = self::flatten($parsed);
            ksort($flattened);

            $result[$matches[1]] = $flattened;
        }

        ksort($result);

        return $result;
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private static function flatten(array $values, string $prefix = ''): array
    {
        $result = [];

        foreach ($values as $key => $value) {
            $path = '' === $prefix ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                /** @var array<string, mixed> $nested */
                $nested = $value;
                $result += self::flatten($nested, $path);

                continue;
            }

            $result[$path] = $value;
        }

        return $result;
    }
}
