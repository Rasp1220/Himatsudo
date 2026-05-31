<?php
declare(strict_types=1);

namespace Himatsudo;

use BEAR\Package\Bootstrap;

require dirname(__DIR__) . '/vendor/autoload.php';

// CORS — allow CMS dev server (and any localhost origin) to call this API
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (str_starts_with($origin, 'http://localhost') || str_starts_with($origin, 'http://127.0.0.1')) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
}
// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri   = $_SERVER['REQUEST_URI'] ?? '/';
$isApi = str_starts_with(strtok($uri, '?'), '/admin/api/');

if (PHP_SAPI === 'cli-server') {
    $context = $isApi ? 'api-app' : 'html-app';
} else {
    $context = $isApi ? 'prod-api-app' : 'prod-html-app';
}

exit((new Bootstrap())->getApp(__NAMESPACE__, $context, dirname(__DIR__))->run());
