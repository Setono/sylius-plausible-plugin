<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<string|null, string|null>
 */
final class ScriptIdentifierTransformer implements DataTransformerInterface
{
    private const IDENTIFIER_PATTERN = '/pa-[a-zA-Z0-9_-]+/';

    public function transform(mixed $value): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        return $value;
    }

    public function reverseTransform(mixed $value): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        if (1 !== preg_match(self::IDENTIFIER_PATTERN, $value, $matches)) {
            $failure = new TransformationFailedException('Could not extract script identifier.');
            $failure->setInvalidMessage('setono_sylius_plausible.plausible_script.invalid');

            throw $failure;
        }

        return $matches[0];
    }
}
