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

### Step 2: Register the tag bag bundle

The plugin outputs its JavaScript through [setono/tag-bag-bundle](https://github.com/Setono/tag-bag-bundle).
If the bundle isn't registered already (Symfony Flex does this for you), add it to `config/bundles.php`:

```php
# config/bundles.php
return [
    // ...
    Setono\TagBagBundle\SetonoTagBagBundle::class => ['all' => true],
];
```

### Step 3: Render the tag bag in your shop layout

Nothing is rendered until you call the tag bag's Twig functions. Add them to your shop layout:

```twig
{# templates/bundles/SyliusShopBundle/layout.html.twig #}
<head>
    {# ... #}
    {{ setono_tag_bag_render_head() }}
</head>
<body>
{{ setono_tag_bag_render_body_begin() }}
    {# ... #}
{{ setono_tag_bag_render_body_end() }}
{{ setono_tag_bag_render_all() }}
</body>
```

> [!IMPORTANT]
> If you skip this step the plugin will appear to work, but no tracking code is ever written to the page.

### Step 4: Add the Plausible script identifier trait to your Channel entity

```php
<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPlausiblePlugin\Model\ChannelInterface as PlausibleChannelInterface;
use Setono\SyliusPlausiblePlugin\Model\ChannelTrait as PlausibleChannelTrait;
use Sylius\Component\Core\Model\Channel as BaseChannel;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel implements PlausibleChannelInterface
{
    use PlausibleChannelTrait;
}
```

Make sure the channel resource points at your own class:

```yaml
# config/packages/sylius_channel.yaml
sylius_channel:
    resources:
        channel:
            classes:
                model: App\Entity\Channel\Channel
```

### Step 5: Import routes

```yaml
# config/routes/setono_sylius_plausible.yaml
setono_sylius_plausible:
    resource: "@SetonoSyliusPlausiblePlugin/Resources/config/routes.yaml"
```

### Step 6: Update your database schema

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

### Self-hosted Plausible

If you run [Plausible Community Edition](https://github.com/plausible/community-edition), point the plugin at your own host:

```yaml
# config/packages/setono_sylius_plausible.yaml
setono_sylius_plausible:
    script_host: 'https://analytics.example.com'
```

The script is then loaded from `https://analytics.example.com/js/<identifier>.js`. Defaults to `https://plausible.io`.
