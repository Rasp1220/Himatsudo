<?php
declare(strict_types=1);

namespace Himatsudo;

use BEAR\Package\Bootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

$uri   = $_SERVER['REQUEST_URI'] ?? '/';
$isApi = str_starts_with(strtok($uri, '?'), '/admin/api/');

if (PHP_SAPI === 'cli-server') {
    $context = $isApi ? 'api-app' : 'html-app';
} else {
    $context = $isApi ? 'prod-api-app' : 'prod-html-app';
}

exit((new Bootstrap())->getApp(__NAMESPACE__, $context, dirname(__DIR__))->run());
