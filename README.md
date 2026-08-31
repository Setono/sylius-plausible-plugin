# Sylius plugin integrating Plausible Analytics

[![Latest Stable Version](http://poser.pugx.org/setono/sylius-plausible-plugin/v)](https://packagist.org/packages/setono/sylius-plausible-plugin)
[![Total Downloads](http://poser.pugx.org/setono/sylius-plausible-plugin/downloads)](https://packagist.org/packages/setono/sylius-plausible-plugin)
[![License](http://poser.pugx.org/setono/sylius-plausible-plugin/license)](https://packagist.org/packages/setono/sylius-plausible-plugin)
[![PHP Version Require](http://poser.pugx.org/setono/sylius-plausible-plugin/require/php)](https://packagist.org/packages/setono/sylius-plausible-plugin)
[![build](https://github.com/Setono/sylius-plausible-plugin/actions/workflows/build.yaml/badge.svg)](https://github.com/Setono/sylius-plausible-plugin/actions/workflows/build.yaml)
[![codecov](https://codecov.io/gh/Setono/sylius-plausible-plugin/graph/badge.svg?token=FUAA6YKXTQ)](https://codecov.io/gh/Setono/sylius-plausible-plugin)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2Fsylius-plausible-plugin%2F2.x)](https://dashboard.stryker-mutator.io/reports/github.com/Setono/sylius-plausible-plugin/2.x)

Use [Plausible Analytics](https://plausible.io) to track visitors and events in your Sylius store.

## Requirements

| | Version |
|---|---|
| PHP | 8.1 – 8.4 |
| Sylius | 1.14 |
| Symfony | 6.4 |

Every combination in that table is covered by the build. Symfony 7 and Sylius 2 are not supported yet.

> [!NOTE]
> The `2.x` branch is the Sylius **1.14** line of the plugin. The branch name tracks the plugin's own
> major version, not Sylius'.

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

## What gets tracked

The plugin sends five custom events. Each one is written to the page as a `plausible(...)` call by
[setono/tag-bag](https://github.com/Setono/tag-bag), so an event triggered by a form submission fires
on the next page the visitor sees.

| Event name | Fires when |
|---|---|
| `Begin Checkout` | The visitor lands on the checkout start route |
| `Address` | The addressing step is completed |
| `Select Shipping Method` | The shipping step is completed |
| `Select Payment Method` | The payment step is completed |
| `Purchase` | The visitor reaches the thank you page |

### Properties

Every event carries the properties of the order it relates to. For the four checkout events that is the
current cart; for `Purchase` it is the completed order.

| Property | Type | Notes |
|---|---|---|
| `order_id` | int | |
| `order_number` | string | |
| `order_total` | float | Major units, i.e. `100.0`, not `10000` |
| `tax_total` | float | |
| `shipping_total` | float | |
| `order_promotion_total` | float | Negative |
| `payment_method` | string | Payment method **code** |
| `shipping_method` | string | Shipping method **code** |
| `coupon_code` | string | |

Properties that are `null` or an empty string are left out, so an order without a coupon has no
`coupon_code` property rather than an empty one. No customer data is sent.

The `Purchase` event additionally carries revenue:

```js
plausible("Purchase", {"props":{"order_id":123},"revenue":{"currency":"USD","amount":100}});
```

> [!NOTE]
> Custom properties and revenue have to be enabled in your Plausible dashboard before they show up. See
> [custom properties](https://plausible.io/docs/custom-props/introduction) and
> [revenue tracking](https://plausible.io/docs/ecommerce-revenue-tracking).

## Tracking your own events

Dispatch the plugin's event and it is turned into a `plausible(...)` call for you:

```php
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;

final class NewsletterSignupHandler
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function __invoke(): void
    {
        $this->eventDispatcher->dispatch(
            (new Event('Newsletter Signup'))->setProperty('source', 'footer'),
        );
    }
}
```

To add properties to the events the plugin already sends, listen to the same event class. The subscriber
that renders the tag runs at priority `-100`, so a listener at the default priority still gets a say:

```php
use Setono\SyliusPlausiblePlugin\Event\Plausible\Event;
use Setono\SyliusPlausiblePlugin\Event\Plausible\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: Event::class)]
final class AddCustomerGroupListener
{
    public function __invoke(Event $event): void
    {
        if (Events::PURCHASE !== $event->getName()) {
            return;
        }

        $event->setProperty('customer_group', 'wholesale');
    }
}
```

> [!WARNING]
> Property values end up inside an inline `<script>` tag. The plugin escapes them, but keep customer
> supplied data out of your properties unless you have a good reason to send it — Plausible is not the
> place for personal data.

