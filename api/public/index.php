<?php
declare(strict_types=1);

namespace Himatsudo\Api;

use BEAR\Package\Bootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

$context = 'prod-api-app';
if (PHP_SAPI === 'cli-server') {
    $context = 'api-app';
}

exit((new Bootstrap())->getApp(__NAMESPACE__, $context, dirname(__DIR__))->run());
