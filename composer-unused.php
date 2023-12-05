<?php

declare(strict_types=1);

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;

return static function (Configuration $config): Configuration {
    return $config
        ->addNamedFilter(NamedFilter::fromString('setono/tag-bag-bundle')) // Used to inject scripts into the page
        ->addNamedFilter(NamedFilter::fromString('symfony/monolog-bundle')) // Used for injecting the logger service
    ;
};
