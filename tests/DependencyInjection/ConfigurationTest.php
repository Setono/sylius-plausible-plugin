<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPlausiblePlugin\DependencyInjection;

use Setono\SyliusPlausiblePlugin\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * See examples of tests and configuration options here: https://github.com/SymfonyTest/SymfonyConfigTest
 */
final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    /**
     * @test
     */
    public function values_are_invalid_if_required_value_is_not_provided(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [], // no values at all
            ],
            '/The child (config|node) "option" (under|at path) "setono_sylius_plausible" must be configured/',
            true,
        );
    }

    /**
     * @test
     */
    public function processed_value_contains_required_value(): void
    {
        $this->assertProcessedConfigurationEquals([
            ['option' => 'first value'],
            ['option' => 'last value'],
        ], [
            'option' => 'last value',
        ]);
    }
}
