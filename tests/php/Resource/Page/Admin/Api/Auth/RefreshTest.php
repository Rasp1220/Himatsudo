<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api\Auth;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Auth\JwtService;
use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Resource\Page\Admin\Api\Auth\Refresh;
use Himatsudo\Service\RefreshTokenService;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RefreshTest extends TestCase
{
    private JwtService $jwtService;
    private ExtendedPdoInterface&MockObject $pdo;
    private UserInterface&MockObject $userService;
    private Refresh $resource;
    private RefreshTokenService $refreshTokenService;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET']        = 'test-jwt-secret-key-for-unit-tests-only-32ch';
        $this->jwtService          = new JwtService();
        $this->pdo                 = $this->createMock(ExtendedPdoInterface::class);
        $this->refreshTokenService = new RefreshTokenService($this->pdo);
        $this->userService         = $this->createMock(UserInterface::class);
        $this->resource            = new Refresh($this->jwtService, $this->refreshTokenService, $this->userService);
    }

    public function testOnPostReturns401ForInvalidToken(): void
    {
        $result = $this->resource->onPost('totally.invalid.token');

        $this->assertSame(401, $result->code);
        $this->assertArrayHasKey('error', $result->body);
    }

    public function testOnPostReturns401WhenTokenNotInDatabase(): void
    {
        $validRefreshToken = $this->jwtService->issueRefreshToken(1);
        $this->pdo->method('fetchOne')->willReturn(false);

        $result = $this->resource->onPost($validRefreshToken);

        $this->assertSame(401, $result->code);
    }

    public function testOnPostReturns401WhenUserNotFound(): void
    {
        $validRefreshToken = $this->jwtService->issueRefreshToken(1);
        $this->pdo->method('fetchOne')->willReturn(['id' => 1, 'user_id' => 1]);
        $this->userService->method('getById')->willReturn(null);

        $result = $this->resource->onPost($validRefreshToken);

        $this->assertSame(401, $result->code);
    }

    public function testOnPostReturns200WithNewTokensOnSuccess(): void
    {
        $validRefreshToken = $this->jwtService->issueRefreshToken(1);
        $stmt              = $this->createMock(PDOStatement::class);

        // findValid returns a row, then delete + save call perform
        $this->pdo->method('fetchOne')->willReturn(['id' => 1, 'user_id' => 1]);
        $this->pdo->method('perform')->willReturn($stmt);

        $this->userService->method('getById')->willReturn([
            'id'    => 1,
            'name'  => 'Alice',
            'email' => 'alice@example.com',
            'role'  => 'admin',
        ]);

        $result = $this->resource->onPost($validRefreshToken);

        $this->assertSame(200, $result->code);
        $this->assertArrayHasKey('access_token', $result->body);
        $this->assertArrayHasKey('refresh_token', $result->body);
    }
}
