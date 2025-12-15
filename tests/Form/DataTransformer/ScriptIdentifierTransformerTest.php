<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Tests\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPlausiblePlugin\Form\DataTransformer\ScriptIdentifierTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class ScriptIdentifierTransformerTest extends TestCase
{
    private ScriptIdentifierTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new ScriptIdentifierTransformer();
    }

    /** @test */
    public function it_transforms_null_to_null(): void
    {
        self::assertNull($this->transformer->transform(null));
    }

    /** @test */
    public function it_transforms_empty_string_to_null(): void
    {
        self::assertNull($this->transformer->transform(''));
    }

    /** @test */
    public function it_transforms_string_to_same_string(): void
    {
        $identifier = 'pa-hb0WlWkUb5U3qhSS-vd-a';
        self::assertSame($identifier, $this->transformer->transform($identifier));
    }

    /** @test */
    public function it_reverse_transforms_null_to_null(): void
    {
        self::assertNull($this->transformer->reverseTransform(null));
    }

    /** @test */
    public function it_reverse_transforms_empty_string_to_null(): void
    {
        self::assertNull($this->transformer->reverseTransform(''));
    }

    /** @test */
    public function it_extracts_identifier_from_identifier_only(): void
    {
        $result = $this->transformer->reverseTransform('pa-hb0WlWkUb5U3qhSS-vd-a');
        self::assertSame('pa-hb0WlWkUb5U3qhSS-vd-a', $result);
    }

    /** @test */
    public function it_extracts_identifier_from_url(): void
    {
        $result = $this->transformer->reverseTransform('https://plausible.io/js/pa-test.js');
        self::assertSame('pa-test', $result);
    }

    /** @test */
    public function it_extracts_identifier_from_html_snippet(): void
    {
        $html = '<script async src="https://plausible.io/js/pa-test.js"></script>';
        self::assertSame('pa-test', $this->transformer->reverseTransform($html));
    }

    /** @test */
    public function it_throws_transformation_failed_exception_for_invalid_input(): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform('invalid-script');
    }

    /** @test */
    public function it_sets_invalid_message_on_transformation_failure(): void
    {
        try {
            $this->transformer->reverseTransform('invalid-script');
            self::fail('Expected TransformationFailedException to be thrown');
        } catch (TransformationFailedException $e) {
            self::assertSame('setono_sylius_plausible.plausible_script.invalid', $e->getInvalidMessage());
        }
    }
}
