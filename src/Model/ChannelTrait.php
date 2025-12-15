<?php

declare(strict_types=1);

namespace Setono\SyliusPlausiblePlugin\Model;

use Doctrine\ORM\Mapping as ORM;

trait ChannelTrait
{
    /** @ORM\Column(type="string", nullable=true) */
    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $plausibleScriptIdentifier = null;

    public function getPlausibleScriptIdentifier(): ?string
    {
        return $this->plausibleScriptIdentifier;
    }

    public function setPlausibleScriptIdentifier(?string $plausibleScriptIdentifier): void
    {
        $this->plausibleScriptIdentifier = $plausibleScriptIdentifier;
    }
}
