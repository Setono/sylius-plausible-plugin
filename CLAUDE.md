# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sylius plugin that integrates Plausible Analytics for tracking visitors and events in Sylius e-commerce stores. The plugin uses the setono/tag-bag bundle to inject JavaScript tracking code.

## Code Standards

Follow clean code principles and SOLID design patterns when working with this codebase:
- Write clean, readable, and maintainable code
- Apply SOLID principles (Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion)
- Use meaningful variable and method names
- Keep methods and classes focused on a single responsibility
- Favor composition over inheritance
- Write code that is easy to test and extend

### Testing Requirements
- Write unit tests for all new functionality (if it makes sense)
- Follow the BDD-style naming convention for test methods (e.g., `it_should_do_something_when_condition_is_met`)
- **MUST use Prophecy for mocking** - Use the `ProphecyTrait` and `$this->prophesize()` for all mocks, NOT PHPUnit's `$this->createMock()`
- **Form testing** - Use Symfony's best practices for form testing as documented at https://symfony.com/doc/current/form/unit_testing.html
  - Extend `Symfony\Component\Form\Test\TypeTestCase` for form type tests
  - Use `$this->factory->create()` to create form instances
  - Test form submission, validation, and data transformation
- Ensure tests are isolated and don't depend on external state
- Test both happy path and edge cases

## Development Commands

### Code Quality & Testing
- `composer analyse` - Run PHPStan static analysis (level max)
- `composer check-style` - Check code style with ECS (Easy Coding Standard)
- `composer fix-style` - Fix code style issues automatically with ECS
- `composer phpunit` - Run PHPUnit tests

```bash
# Run a single test file
vendor/bin/phpunit tests/Path/To/TestFile.php

# Run a single test method
vendor/bin/phpunit --filter testMethodName

# Run Rector (PHP upgrades)
vendor/bin/rector process --dry-run
vendor/bin/rector process

# Lint YAML/Twig (requires test application)
(cd tests/Application && bin/console lint:yaml ../../src/Resources)
(cd tests/Application && bin/console lint:twig ../../src/Resources)

# Lint container
(cd tests/Application && bin/console lint:container)
```

### Static Analysis

#### PHPStan Configuration
PHPStan is configured in `phpstan.neon` with:
- **Analysis Level**: max (strictest)
- **Extensions**: Auto-loaded via `phpstan/extension-installer`
  - `phpstan/phpstan-symfony` - Symfony framework integration
  - `phpstan/phpstan-doctrine` - Doctrine ORM integration
  - `phpstan/phpstan-phpunit` - PHPUnit test integration
  - `jangregor/phpstan-prophecy` - Prophecy mocking integration
- **Symfony Integration**: Uses console application loader (`tests/PHPStan/console_application.php`)
- **Doctrine Integration**: Uses object manager loader (`tests/PHPStan/object_manager.php`)
- **Baseline**: Generate with `composer analyse -- --generate-baseline` to track improvements

### Test Application
The plugin includes a test Symfony application in `tests/Application/` for development and testing:
- Navigate to `tests/Application/` directory
- Run `yarn install && yarn build` to build assets
- Use standard Symfony commands for the test app
- **Sylius Backend Credentials**: Username: `sylius`, Password: `sylius`

## Bash Tools Recommendations

Use the right tool for the right job when executing bash commands:

- **Finding FILES?** → Use `fd` (fast file finder)
- **Finding TEXT/strings?** → Use `rg` (ripgrep for text search)
- **Finding CODE STRUCTURE?** → Use `ast-grep` (syntax-aware code search)
- **SELECTING from multiple results?** → Pipe to `fzf` (interactive fuzzy finder)
- **Interacting with JSON?** → Use `jq` (JSON processor)
- **Interacting with YAML or XML?** → Use `yq` (YAML/XML processor)

Examples:
- `fd "*.php" | fzf` - Find PHP files and interactively select one
- `rg "function.*validate" | fzf` - Search for validation functions and select
- `ast-grep --lang php -p 'class $name extends $parent'` - Find class inheritance patterns

## Architecture

### Event-Driven Tracking Flow

The plugin tracks e-commerce events through a two-layer event system:

1. **Sylius Events → Plausible Events**: Event subscribers in `src/EventSubscriber/` listen to Sylius checkout events and dispatch internal Plausible events
2. **Plausible Events → Tag Bag**: `ClientSide/EventSubscriber` and `ClientSide/LibrarySubscriber` convert Plausible events into JavaScript tags via setono/tag-bag

Key subscribers:
- `BeginCheckoutSubscriber` - Tracks checkout start
- `AddressSubscriber` - Tracks address entry
- `SelectShippingMethodSubscriber` / `SelectPaymentMethodSubscriber` - Tracks method selections
- `PurchaseSubscriber` - Tracks completed purchases with revenue
- `PopulateOrderRelatedPropertiesSubscriber` - Enriches events with order data

### Plausible Event Model

Located in `src/Event/Plausible/`:
- `Event` - Single Plausible event with name, properties, and optional revenue
- `Events` - Collection of events (implements IteratorAggregate)
- `Properties` - Key-value pairs for event properties
- `Revenue` - Revenue data with currency and amount

### Configuration

Bundle configuration under `setono_sylius_plausible`:
- `client_side.enabled` - Enable/disable tracking
- `client_side.script` - Plausible script URL (supports script extensions)
- `domain` - Override domain for testing

## PHP Version

Minimum PHP 8.1. Rector is configured for PHP 8.1 level upgrades.
