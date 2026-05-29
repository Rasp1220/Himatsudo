<?php
declare(strict_types=1);

namespace Himatsudo\Frontend;

use BEAR\Package\Bootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

$context = 'prod-html-app';
if (PHP_SAPI === 'cli-server') {
    $context = 'html-app';
}

exit((new Bootstrap())->getApp(__NAMESPACE__, $context, dirname(__DIR__))->run());
