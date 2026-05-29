<?php
declare(strict_types=1);

namespace Himatsudo\Api\Interceptor;

use Himatsudo\Api\Auth\JwtService;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Throwable;

final class AuthInterceptor implements MethodInterceptor
{
    public function __construct(private readonly JwtService $jwtService)
    {
    }

    public function invoke(MethodInvocation $invocation): mixed
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!str_starts_with($header, 'Bearer ')) {
            return $this->unauthorized($invocation, 'Authorization header missing');
        }

        $token = substr($header, 7);

        try {
            $claims = $this->jwtService->validateAccessToken($token);
        } catch (Throwable) {
            return $this->unauthorized($invocation, 'Invalid or expired token');
        }

        // Store claims in request globals so resources can read them
        $_REQUEST['_auth_uid']  = $claims['uid'];
        $_REQUEST['_auth_role'] = $claims['role'];

        return $invocation->proceed();
    }

    private function unauthorized(MethodInvocation $invocation, string $message): object
    {
        $resource = $invocation->getThis();
        $resource->code = 401;
        $resource->body = ['error' => 'Unauthorized', 'message' => $message];
        return $resource;
    }
}
