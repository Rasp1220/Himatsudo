<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api\Auth;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Resource\Page\Admin\Api\Auth\Logout;
use Himatsudo\Service\RefreshTokenService;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LogoutTest extends TestCase
{
    private ExtendedPdoInterface&MockObject $pdo;
    private Logout $resource;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(ExtendedPdoInterface::class);
        // RefreshTokenService is final, so we use a real instance with a mocked PDO
        $refreshTokenService = new RefreshTokenService($this->pdo);
        $this->resource      = new Logout($refreshTokenService);
    }

    public function testOnPostWithRefreshTokenCallsDeleteAndReturns204(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->expects($this->once())
            ->method('perform')
            ->with($this->stringContains('DELETE FROM refresh_tokens'), $this->arrayHasKey('token'))
            ->willReturn($stmt);

        $result = $this->resource->onPost('my-refresh-token');

        $this->assertSame(204, $result->code);
    }

    public function testOnPostWithoutRefreshTokenDoesNotCallDeleteAndReturns204(): void
    {
        $this->pdo->expects($this->never())->method('perform');

        $result = $this->resource->onPost(null);

        $this->assertSame(204, $result->code);
    }
}
