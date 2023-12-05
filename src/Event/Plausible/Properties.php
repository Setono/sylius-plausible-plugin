<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Event\Plausible;

use Webmozart\Assert\Assert;

/**
 * @implements \IteratorAggregate<string, mixed>
 * @implements \ArrayAccess<string, mixed>
 */
final class Properties implements \Countable, \JsonSerializable, \IteratorAggregate, \ArrayAccess
{
    /** @var array<string, mixed> */
    private array $properties = [];

    public function get(string $name): mixed
    {
        Assert::true($this->has($name), sprintf('The property with key "%s" does not exist', $name));

        return $this->properties[$name];
    }

    public function set(string $name, mixed $value): static
    {
        $this->properties[$name] = $value;

        return $this;
    }

    /**
     * @psalm-assert-if-true mixed $this->properties[$name]
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->properties);
    }

    public function isEmpty(): bool
    {
        return [] === $this->properties;
    }

    public function count(): int
    {
        return count($this->properties);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->properties);
    }

    /**
     * @psalm-assert non-empty-string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        self::assertOffset($offset);

        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        self::assertOffset($offset);

        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        self::assertOffset($offset);

        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        Assert::true($this->offsetExists($offset), sprintf('The property with key "%s" does not exist', $offset));

        unset($this->properties[$offset]);
    }

    public function jsonSerialize(): ?array
    {
        return $this->properties;
    }

    /**
     * @psalm-assert non-empty-string $offset
     */
    private static function assertOffset(mixed $offset): void
    {
        Assert::stringNotEmpty($offset, 'Properties are key value pairs, where the key must be a string');
    }
}
