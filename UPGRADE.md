# Upgrade from 1.x to 2.0

Version 2.0 moves the Plausible configuration out of YAML and into the channel, so a store with
several channels can report into a separate Plausible site per channel. It also moves to Plausible's
current script format.

Nothing is migrated automatically. Work through the steps below.

## 1. Get a new script from Plausible

1.x used Plausible's legacy script, identified by a URL and a `data-domain` attribute:

```html
<script defer data-domain="example.com" src="https://plausible.io/js/script.manual.revenue.js"></script>
```

2.0 uses Plausible's current script, identified by a `pa-` identifier:

```html
<script defer src="https://plausible.io/js/pa-hb0WlWkUb5U3qhSS-vd-a.js"></script>
```

Open your site in the Plausible dashboard and copy the snippet it gives you. **A legacy
`script.*.js` URL is rejected by the plugin**, so you cannot reuse your old configuration.

> [!NOTE]
> Script extensions are no longer selected through the URL. Which features are enabled is now a
> per-site setting in the Plausible dashboard.

## 2. Replace the old configuration

```diff
 # config/packages/setono_sylius_plausible.yaml
 setono_sylius_plausible:
-    client_side:
-        script: "https://plausible.io/js/script.manual.revenue.js"
-    domain: "example.com"
```

Both `client_side.script` and `domain` are gone, and leaving them in place is a configuration error —
the container will refuse to build.

The domain is no longer sent at all. Plausible resolves the site from the script identifier, which is
what makes per-channel sites possible.

Two options replace parts of the old configuration:

| 1.x | 2.0 |
|---|---|
| `client_side.enabled: false` | `enabled: false` |
| A self-hosted URL in `client_side.script` | `script_host: 'https://analytics.example.com'` |

```yaml
setono_sylius_plausible:
    enabled: true                          # default
    script_host: 'https://plausible.io'    # default
```

## 3. Make your channel Plausible aware

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

If you do not already have your own channel class, create one and point the resource at it:

```yaml
# config/packages/sylius_channel.yaml
sylius_channel:
    resources:
        channel:
            classes:
                model: App\Entity\Channel\Channel
```

## 4. Import the plugin's routes

New in 2.0 — the admin screens do not exist without it:

```yaml
# config/routes/setono_sylius_plausible.yaml
setono_sylius_plausible:
    resource: "@SetonoSyliusPlausiblePlugin/Resources/config/routes.yaml"
```

## 5. Update your database schema

Two changes: a `plausible_script_identifier` column on `sylius_channel`, and a new
`setono_sylius_plausible__notification_dismissal` table.

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

## 6. Configure each channel

Go to **Marketing > Plausible** in the admin panel and paste the identifier, the full script URL or
the whole HTML snippet for each channel — all three are accepted and normalized to the identifier.

Until every enabled channel is configured, a notification on the admin dashboard reminds you which
ones are still missing.

## Renamed classes

Only relevant if you referenced them directly:

| 1.x | 2.0 |
|---|---|
| `EventSubscriber\ClientSide\LibrarySubscriber` | `EventSubscriber\PlausibleLibrarySubscriber` |
| `EventSubscriber\ClientSide\EventSubscriber` | `EventSubscriber\PlausibleEventSubscriber` |

The `client_side` naming is gone throughout: there is no server side tracking planned, so the
distinction only added a layer to every name.

`Event\Plausible\Event`, `Events`, `Properties` and `Revenue` are unchanged, so any listener you
wrote against `Event::class` keeps working.
