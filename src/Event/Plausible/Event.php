<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Event\Plausible;

use Sylius\Component\Core\Model\OrderInterface;

final class Event
{
    private Properties $properties;

    private ?Revenue $revenue = null;

    /**
     * The order this event relates to, when it is known by the subscriber dispatching the event.
     *
     * Listeners that enrich the event should prefer this order over any order they resolve
     * themselves: on the thank you page the completed order is no longer the current cart.
     */
    private ?OrderInterface $order = null;

    public function __construct(private readonly string $name)
    {
        $this->properties = new Properties();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProperties(): Properties
    {
        return $this->properties;
    }

    public function setProperties(Properties $properties): static
    {
        $this->properties = $properties;

        return $this;
    }

    public function getProperty(string $name): mixed
    {
        return $this->properties->get($name);
    }

    public function setProperty(string $name, mixed $value): static
    {
        $this->properties->set($name, $value);

        return $this;
    }

    public function hasProperty(string $name): bool
    {
        return $this->properties->has($name);
    }

    public function getRevenue(): ?Revenue
    {
        return $this->revenue;
    }

    public function setRevenue(string $currency, float $amount): static
    {
        $this->revenue = new Revenue($currency, $amount);

        return $this;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function setOrder(?OrderInterface $order): static
    {
        $this->order = $order;

        return $this;
    }
}
