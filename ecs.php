<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {
    $config->import('vendor/sylius-labs/coding-standard/ecs.php');
    $config->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // absolute paths, for consistency with rector.php and so the skip list keeps working
    // regardless of the directory the tool is invoked from
    $config->skip([
        __DIR__ . '/tests/Application/node_modules/*',
        __DIR__ . '/tests/Application/public/*',
        __DIR__ . '/tests/Application/var/*',
    ]);
};
