<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/engine',
    ]);

    // register a single rule
    // $rectorConfig->rule(\Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector::class);

    // Define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_74
    ]);
};
