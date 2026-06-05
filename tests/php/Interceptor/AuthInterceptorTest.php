<?php
declare(strict_types=1);

namespace Himatsudo\Tests\Interceptor;

use Himatsudo\Auth\JwtService;
use Himatsudo\Interceptor\AuthInterceptor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ray\Aop\MethodInvocation;

/**
 * mirrors src/Interceptor/AuthInterceptor.php
 *
 * JwtService is final and cannot be mocked — we use a real instance with a
 * known test secret and issue real tokens from it.
 */
class AuthInterceptorTest extends TestCase
{
    private JwtService $jwt;
    private AuthInterceptor $interceptor;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test-jwt-secret-key-for-unit-tests-only-32ch';
        $this->jwt         = new JwtService();
        $this->interceptor = new AuthInterceptor($this->jwt);
        unset($_SERVER['HTTP_AUTHORIZATION']);
        $_REQUEST = [];
    }

    private function makeInvocation(): MethodInvocation&MockObject
    {
        $resource       = new \stdClass();
        $resource->code = 200;
        $resource->body = [];

        $invocation = $this->createMock(MethodInvocation::class);
        $invocation->method('getThis')->willReturn($resource);
        $invocation->method('proceed')->willReturn($resource);

        return $invocation;
    }

    public function testProceedsWhenValidBearerToken(): void
    {
        $token = $this->jwt->issueAccessToken(1, 'admin');
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer {$token}";

        $invocation = $this->makeInvocation();
        $invocation->expects($this->once())->method('proceed');

        $this->interceptor->invoke($invocation);

        $this->assertSame(1, $_REQUEST['_auth_uid']);
        $this->assertSame('admin', $_REQUEST['_auth_role']);
    }

    public function testReturns401WhenAuthorizationHeaderMissing(): void
    {
        unset($_SERVER['HTTP_AUTHORIZATION']);

        $invocation = $this->makeInvocation();
        $invocation->expects($this->never())->method('proceed');

        $resource = $this->interceptor->invoke($invocation);

        $this->assertSame(401, $resource->code);
    }

    public function testReturns401WhenTokenHasWrongScheme(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic dXNlcjpwYXNz';

        $invocation = $this->makeInvocation();
        $invocation->expects($this->never())->method('proceed');

        $resource = $this->interceptor->invoke($invocation);

        $this->assertSame(401, $resource->code);
    }

    public function testReturns401WhenTokenIsInvalid(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer this.is.not.a.valid.jwt';

        $invocation = $this->makeInvocation();
        $invocation->expects($this->never())->method('proceed');

        $resource = $this->interceptor->invoke($invocation);

        $this->assertSame(401, $resource->code);
        $this->assertArrayHasKey('error', $resource->body);
    }

    public function testReturns401WhenRefreshTokenUsedAsAccessToken(): void
    {
        // A refresh token must be rejected by the interceptor (it validates access tokens)
        $refreshToken = $this->jwt->issueRefreshToken(1);
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer {$refreshToken}";

        $invocation = $this->makeInvocation();
        $invocation->expects($this->never())->method('proceed');

        $resource = $this->interceptor->invoke($invocation);

        $this->assertSame(401, $resource->code);
    }

    public function testStoresClaimsInRequestGlobals(): void
    {
        $token = $this->jwt->issueAccessToken(42, 'editor');
        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer {$token}";

        $invocation = $this->makeInvocation();
        $this->interceptor->invoke($invocation);

        $this->assertSame(42, $_REQUEST['_auth_uid']);
        $this->assertSame('editor', $_REQUEST['_auth_role']);
    }
}
