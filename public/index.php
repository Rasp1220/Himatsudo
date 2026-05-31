<?php
declare(strict_types=1);

namespace Himatsudo;

use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Router\RouterInterface;
use BEAR\Sunday\Extension\Transfer\HttpCacheInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use Throwable;

require dirname(__DIR__) . '/vendor/autoload.php';

// CORS — allow CMS dev server (and any localhost origin) to call this API
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (str_starts_with($origin, 'http://localhost') || str_starts_with($origin, 'http://127.0.0.1')) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
}
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

$appDir  = dirname(__DIR__);
$injector = Injector::getInstance(__NAMESPACE__, $context, $appDir);

$injector->getInstance(HttpCacheInterface::class);
$router  = $injector->getInstance(RouterInterface::class);
$request = $router->match($GLOBALS, $_SERVER);

try {
    $resource  = $injector->getInstance(ResourceInterface::class);
    $ro        = $resource->{$request->method}->uri($request->path)($request->query);
    $responder = $injector->getInstance(TransferInterface::class);
    $responder($ro, $_SERVER);
    exit(0);
} catch (Throwable $e) {
    http_response_code(500);
    $detail = [
        'error' => $e->getMessage(),
        'class' => $e::class,
        'file'  => str_replace(dirname(__DIR__), '', $e->getFile()) . ':' . $e->getLine(),
        'trace' => array_slice(array_map(
            static fn($f) => ($f['class'] ?? '') . ($f['type'] ?? '') . ($f['function'] ?? '') . ' ' . str_replace(dirname(__DIR__), '', $f['file'] ?? '') . ':' . ($f['line'] ?? ''),
            $e->getTrace()
        ), 0, 8),
    ];
    echo json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit(1);
}
