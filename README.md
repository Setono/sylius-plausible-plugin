# Sylius plugin integrating Plausible Analytics

[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Mutation testing][ico-infection]][link-infection]

Use [Plausible Analytics](https://plausible.io) to track visitors and events in your Sylius store.

## Installation

### Step 1: Install and enable the plugin

```bash
composer require setono/sylius-plausible-plugin
```

## Usage

If you have created your website in the Plausible dashboard, the plugin will just work out of the box. Enjoy 🎉

## Configuration

### Add functionality

Plausible use different scripts to enable functionality. Plausible calls them script extensions, and you can read
about them [here](https://plausible.io/docs/script-extensions).

To use a script extension, you need to configure the script in the plugin as follows:

```yaml
setono_sylius_plausible:
    client_side:
        script: "https://plausible.io/js/script.manual.revenue.file-downloads.js"
``` 

Here I have added the 'file downloads' extension. Notice that I am keeping both the 'manual' and 'revenue' extensions.
This is because the 'revenue' extension is used for tracking purchases, and the 'manual' extension is used for manual
tracking of the pageview event.

### Test tracking

If you want to test the plugin in your local environment, you can input the domain and use the local script extension:

```yaml
setono_sylius_plausible:
    client_side:
        script: "https://plausible.io/js/script.manual.revenue.local.js"
    domain: "your-domain.com"
``` 

[ico-version]: https://poser.pugx.org/setono/sylius-plausible-plugin/v/stable
[ico-license]: https://poser.pugx.org/setono/sylius-plausible-plugin/license
[ico-github-actions]: https://github.com/Setono/sylius-plausible-plugin/workflows/build/badge.svg
[ico-code-coverage]: https://codecov.io/gh/Setono/sylius-plausible-plugin/branch/master/graph/badge.svg
[ico-infection]: https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2Fsylius-plausible-plugin%2Fmaster

[link-packagist]: https://packagist.org/packages/setono/sylius-plausible-plugin
[link-github-actions]: https://github.com/Setono/sylius-plausible-plugin/actions
[link-code-coverage]: https://codecov.io/gh/Setono/sylius-plausible-plugin
[link-infection]: https://dashboard.stryker-mutator.io/reports/github.com/Setono/sylius-plausible-plugin/master
