<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Auth;

use Himatsudo\Auth\JwtService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * mirrors src/Auth/JwtService.php
 */
class JwtServiceTest extends TestCase
{
    private JwtService $jwt;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test-jwt-secret-key-for-unit-tests-only-32ch';
        $this->jwt          = new JwtService();
    }

    public function testIssueAccessTokenReturnsNonEmptyString(): void
    {
        $token = $this->jwt->issueAccessToken(1, 'admin');
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testValidateAccessTokenReturnsCorrectClaims(): void
    {
        $token  = $this->jwt->issueAccessToken(42, 'editor');
        $claims = $this->jwt->validateAccessToken($token);

        $this->assertSame(42, $claims['uid']);
        $this->assertSame('editor', $claims['role']);
    }

    public function testIssueRefreshTokenReturnsNonEmptyString(): void
    {
        $token = $this->jwt->issueRefreshToken(1);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testValidateRefreshTokenReturnsUserId(): void
    {
        $token  = $this->jwt->issueRefreshToken(99);
        $userId = $this->jwt->validateRefreshToken($token);

        $this->assertSame(99, $userId);
    }

    public function testAccessTokenRejectedAsRefreshToken(): void
    {
        $this->expectException(RuntimeException::class);
        $token = $this->jwt->issueAccessToken(1, 'admin');
        $this->jwt->validateRefreshToken($token);
    }

    public function testRefreshTokenRejectedAsAccessToken(): void
    {
        $this->expectException(RuntimeException::class);
        $token = $this->jwt->issueRefreshToken(1);
        $this->jwt->validateAccessToken($token);
    }

    public function testTamperedTokenThrows(): void
    {
        $this->expectException(\Throwable::class);
        $this->jwt->validateAccessToken('invalid.token.value');
    }

    public function testAccessTokensForDifferentUsersHaveDifferentUids(): void
    {
        $t1 = $this->jwt->issueAccessToken(1, 'admin');
        $t2 = $this->jwt->issueAccessToken(2, 'editor');

        $c1 = $this->jwt->validateAccessToken($t1);
        $c2 = $this->jwt->validateAccessToken($t2);

        $this->assertNotSame($c1['uid'], $c2['uid']);
    }
}
