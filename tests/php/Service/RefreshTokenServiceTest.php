<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Service;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Service\RefreshTokenService;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RefreshTokenServiceTest extends TestCase
{
    private ExtendedPdoInterface&MockObject $pdo;
    private RefreshTokenService $service;

    protected function setUp(): void
    {
        $this->pdo     = $this->createMock(ExtendedPdoInterface::class);
        $this->service = new RefreshTokenService($this->pdo);
    }

    public function testSaveCallsPerformWithInsertSql(): void
    {
        $capturedSql    = '';
        $capturedParams = [];
        $stmt           = $this->createMock(PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('perform')
            ->willReturnCallback(function (string $sql, array $params = []) use ($stmt, &$capturedSql, &$capturedParams) {
                $capturedSql    = $sql;
                $capturedParams = $params;
                return $stmt;
            });

        $this->service->save(1, 'mytoken', '2026-01-01 00:00:00');

        $this->assertStringContainsString('INSERT INTO refresh_tokens', $capturedSql);
        $this->assertSame(1, $capturedParams['user_id']);
        $this->assertSame('mytoken', $capturedParams['token']);
        $this->assertSame('2026-01-01 00:00:00', $capturedParams['expires_at']);
    }

    public function testFindValidReturnsArrayWhenRowFound(): void
    {
        $row = ['id' => 1, 'user_id' => 2, 'token' => 'abc', 'expires_at' => '2099-01-01'];
        $this->pdo->method('fetchOne')->willReturn($row);

        $result = $this->service->findValid('abc');

        $this->assertSame($row, $result);
    }

    public function testFindValidReturnsNullWhenNotFound(): void
    {
        $this->pdo->method('fetchOne')->willReturn(false);

        $result = $this->service->findValid('nonexistent');

        $this->assertNull($result);
    }

    public function testDeleteCallsPerformWithDeleteSql(): void
    {
        $capturedSql = '';
        $stmt        = $this->createMock(PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('perform')
            ->willReturnCallback(function (string $sql) use ($stmt, &$capturedSql) {
                $capturedSql = $sql;
                return $stmt;
            });

        $this->service->delete('mytoken');

        $this->assertStringContainsString('DELETE FROM refresh_tokens', $capturedSql);
        $this->assertStringContainsString('token', $capturedSql);
    }

    public function testDeleteExpiredCallsPerformWithExpiresAtCondition(): void
    {
        $capturedSql = '';
        $stmt        = $this->createMock(PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('perform')
            ->willReturnCallback(function (string $sql) use ($stmt, &$capturedSql) {
                $capturedSql = $sql;
                return $stmt;
            });

        $this->service->deleteExpired();

        $this->assertStringContainsString('DELETE FROM refresh_tokens', $capturedSql);
        $this->assertStringContainsString('expires_at', $capturedSql);
    }
}
