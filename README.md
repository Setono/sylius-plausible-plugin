# Sylius plugin integrating Plausible Analytics

[![Latest Stable Version](http://poser.pugx.org/setono/sylius-plausible-plugin/v)](https://packagist.org/packages/setono/sylius-plausible-plugin)
[![Total Downloads](http://poser.pugx.org/setono/sylius-plausible-plugin/downloads)](https://packagist.org/packages/setono/sylius-plausible-plugin)
[![License](http://poser.pugx.org/setono/sylius-plausible-plugin/license)](https://packagist.org/packages/setono/sylius-plausible-plugin)
[![PHP Version Require](http://poser.pugx.org/setono/sylius-plausible-plugin/require/php)](https://packagist.org/packages/setono/sylius-plausible-plugin)
[![build](https://github.com/Setono/sylius-plausible-plugin/actions/workflows/build.yaml/badge.svg)](https://github.com/Setono/sylius-plausible-plugin/actions/workflows/build.yaml)
[![codecov](https://codecov.io/gh/Setono/sylius-plausible-plugin/graph/badge.svg?token=FUAA6YKXTQ)](https://codecov.io/gh/Setono/sylius-plausible-plugin)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2Fsylius-plausible-plugin%2F2.x)](https://dashboard.stryker-mutator.io/reports/github.com/Setono/sylius-plausible-plugin/2.x)

Use [Plausible Analytics](https://plausible.io) to track visitors and events in your Sylius store.

## Installation

### Step 1: Install and enable the plugin

```bash
composer require setono/sylius-plausible-plugin
```

### Step 2: Add the Plausible script identifier trait to your Channel entity

```php
<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPlausiblePlugin\Model\ChannelInterface as PlausibleChannelInterface;
use Setono\SyliusPlausiblePlugin\Model\ChannelPlausibleAwareTrait;
use Sylius\Component\Core\Model\Channel as BaseChannel;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel implements PlausibleChannelInterface
{
    use ChannelPlausibleAwareTrait;
}
```

### Step 3: Update your database schema

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

## Usage

### Configure Plausible per channel

Navigate to **Marketing > Plausible** in the admin panel to configure the Plausible script for each channel.

You can enter the Plausible script in any of the following formats:

- **Identifier only**: `pa-hb0WlWkUb5U3qhSS-vd-a`
- **Full URL**: `https://plausible.io/js/pa-hb0WlWkUb5U3qhSS-vd-a.js`
- **HTML snippet**: `<script async src="https://plausible.io/js/pa-hb0WlWkUb5U3qhSS-vd-a.js"></script>`

The plugin will normalize any of these formats and output the correct script tag on your storefront.
