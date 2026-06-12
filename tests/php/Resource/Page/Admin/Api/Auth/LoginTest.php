<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api\Auth;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Auth\JwtService;
use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Resource\Page\Admin\Api\Auth\Login;
use Himatsudo\Service\RefreshTokenService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private UserInterface&MockObject $userService;
    private JwtService $jwtService;
    private Login $resource;

    /** Tracks save() calls on the real RefreshTokenService */
    private bool $saveCalled = false;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test-jwt-secret-key-for-unit-tests-only-32ch';

        $this->userService = $this->createMock(UserInterface::class);
        $this->jwtService  = new JwtService();

        // RefreshTokenService is final, so we use a real instance backed by an in-memory PDO mock
        $pdo = $this->createMock(ExtendedPdoInterface::class);
        $pdo->method('perform')->willReturn(new \PDOStatement());
        $refreshTokenService = new RefreshTokenService($pdo);

        $this->resource = new Login($this->userService, $refreshTokenService, $this->jwtService);
    }

    public function testOnPostReturns401WhenUserNotFound(): void
    {
        $this->userService->method('getByEmail')->willReturn(null);

        $result = $this->resource->onPost('nobody@example.com', 'password');

        $this->assertSame(401, $result->code);
        $this->assertArrayHasKey('error', $result->body);
    }

    public function testOnPostReturns401WhenPasswordWrong(): void
    {
        $user = ['id' => 1, 'password' => 'hashed', 'role' => 'editor', 'name' => 'Alice', 'email' => 'alice@example.com'];
        $this->userService->method('getByEmail')->willReturn($user);
        $this->userService->method('verifyPassword')->willReturn(false);

        $result = $this->resource->onPost('alice@example.com', 'wrongpassword');

        $this->assertSame(401, $result->code);
    }

    public function testOnPostReturns200WithTokensWhenCredentialsValid(): void
    {
        $user = ['id' => 1, 'password' => 'hashed', 'role' => 'admin', 'name' => 'Alice', 'email' => 'alice@example.com'];
        $this->userService->method('getByEmail')->willReturn($user);
        $this->userService->method('verifyPassword')->willReturn(true);

        $result = $this->resource->onPost('alice@example.com', 'correctpassword');

        $this->assertSame(200, $result->code);
        $this->assertArrayHasKey('access_token', $result->body);
        $this->assertArrayHasKey('refresh_token', $result->body);
        $this->assertArrayHasKey('user', $result->body);
        $this->assertSame(1, $result->body['user']['id']);
    }
}
