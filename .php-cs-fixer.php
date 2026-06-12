<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests/php'])
    ->name('*.php');

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                       => true,
        '@PHP81Migration'              => true,
        'declare_strict_types'         => true,
        'no_unused_imports'            => true,
        'ordered_imports'              => ['sort_algorithm' => 'alpha'],
        'array_syntax'                 => ['syntax' => 'short'],
        'trailing_comma_in_multiline'  => true,
        'no_extra_blank_lines'         => true,
        'single_quote'                 => true,
        'binary_operator_spaces'       => ['default' => 'align_single_space_minimal'],
    ])
    ->setFinder($finder);
