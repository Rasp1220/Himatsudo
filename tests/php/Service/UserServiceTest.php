<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Service;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Service\UserService;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * mirrors src/Service/UserService.php
 */
class UserServiceTest extends TestCase
{
    private ExtendedPdoInterface&MockObject $pdo;
    private UserService $service;

    protected function setUp(): void
    {
        $this->pdo     = $this->createMock(ExtendedPdoInterface::class);
        $this->service = new UserService($this->pdo);
    }

    private function makeUser(int $id = 1): array
    {
        return [
            'id'         => $id,
            'name'       => 'Test User',
            'email'      => 'test@example.com',
            'role'       => 'editor',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];
    }

    public function testGetListReturnsPaginatedResult(): void
    {
        $this->pdo->method('fetchAll')->willReturn([$this->makeUser()]);
        $this->pdo->method('fetchValue')->willReturn('1');

        $result = $this->service->getList(1, 20);

        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('last_page', $result);
        $this->assertCount(1, $result['items']);
        $this->assertSame(1, $result['total']);
    }

    public function testGetListPaginationCalculation(): void
    {
        $this->pdo->method('fetchAll')->willReturn([]);
        $this->pdo->method('fetchValue')->willReturn('45');

        $result = $this->service->getList(1, 20);

        $this->assertSame(45, $result['total']);
        $this->assertSame(3, $result['last_page']);
    }

    public function testGetByIdReturnsUserWhenFound(): void
    {
        $this->pdo->method('fetchOne')->willReturn($this->makeUser(5));

        $result = $this->service->getById(5);

        $this->assertNotNull($result);
        $this->assertSame(5, $result['id']);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $this->pdo->method('fetchOne')->willReturn(false);

        $result = $this->service->getById(999);

        $this->assertNull($result);
    }

    public function testGetPublicListReturnsRows(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'Admin', 'avatar' => null, 'bio' => null],
            ['id' => 2, 'name' => 'Editor', 'avatar' => '/a.png', 'bio' => 'hi'],
        ];
        $this->pdo->method('fetchAll')->willReturn($rows);

        $this->assertSame($rows, $this->service->getPublicList());
    }

    public function testGetPublicByIdReturnsUserWhenFound(): void
    {
        $row = ['id' => 5, 'name' => 'Author', 'avatar' => null, 'bio' => 'bio'];
        $this->pdo->method('fetchOne')->willReturn($row);

        $result = $this->service->getPublicById(5);

        $this->assertSame(5, $result['id']);
    }

    public function testGetPublicByIdReturnsNullWhenNotFound(): void
    {
        $this->pdo->method('fetchOne')->willReturn(false);

        $this->assertNull($this->service->getPublicById(999));
    }

    public function testUpdateAllowsAvatarAndBioFields(): void
    {
        $capturedSql = null;
        $stmt        = $this->createMock(PDOStatement::class);
        $this->pdo->method('perform')
            ->willReturnCallback(function (string $sql) use ($stmt, &$capturedSql) {
                $capturedSql = $sql;
                return $stmt;
            });
        $this->pdo->method('fetchOne')->willReturn($this->makeUser());

        $this->service->update(1, ['avatar' => '/x.png', 'bio' => 'about me']);

        $this->assertStringContainsString('avatar = :avatar', $capturedSql);
        $this->assertStringContainsString('bio = :bio', $capturedSql);
    }

    public function testUpdateAllowsSnsFields(): void
    {
        $capturedSql = null;
        $stmt        = $this->createMock(PDOStatement::class);
        $this->pdo->method('perform')
            ->willReturnCallback(function (string $sql) use ($stmt, &$capturedSql) {
                $capturedSql = $sql;
                return $stmt;
            });
        $this->pdo->method('fetchOne')->willReturn($this->makeUser());

        $this->service->update(1, [
            'instagram_url' => 'https://instagram.com/test',
            'twitter_url'   => 'https://x.com/test',
            'tiktok_url'    => 'https://tiktok.com/@test',
        ]);

        $this->assertStringContainsString('instagram_url = :instagram_url', $capturedSql);
        $this->assertStringContainsString('twitter_url = :twitter_url', $capturedSql);
        $this->assertStringContainsString('tiktok_url = :tiktok_url', $capturedSql);
    }

    public function testGetByEmailReturnsUserWhenFound(): void
    {
        $user = $this->makeUser();
        $this->pdo->method('fetchOne')->willReturn($user);

        $result = $this->service->getByEmail('test@example.com');

        $this->assertNotNull($result);
        $this->assertSame('test@example.com', $result['email']);
    }

    public function testGetByEmailReturnsNullWhenNotFound(): void
    {
        $this->pdo->method('fetchOne')->willReturn(false);

        $result = $this->service->getByEmail('nobody@example.com');

        $this->assertNull($result);
    }

    public function testCreateReturnsNewUser(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->method('perform')->willReturn($stmt);
        $this->pdo->method('lastInsertId')->willReturn('10');
        $this->pdo->method('fetchOne')->willReturn($this->makeUser(10));

        $result = $this->service->create('New User', 'new@example.com', 'password123');

        $this->assertSame(10, $result['id']);
    }

    public function testCreateHashesPassword(): void
    {
        $capturedBind = null;
        $stmt         = $this->createMock(PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('perform')
            ->willReturnCallback(function (string $sql, array $bind) use ($stmt, &$capturedBind) {
                $capturedBind = $bind;
                return $stmt;
            });
        $this->pdo->method('lastInsertId')->willReturn('1');
        $this->pdo->method('fetchOne')->willReturn($this->makeUser());

        $this->service->create('User', 'user@example.com', 'plaintext');

        $this->assertNotSame('plaintext', $capturedBind['password']);
        $this->assertTrue(password_verify('plaintext', $capturedBind['password']));
    }

    public function testUpdateReturnsModifiedUser(): void
    {
        $stmt    = $this->createMock(PDOStatement::class);
        $updated = array_merge($this->makeUser(), ['name' => 'Updated Name']);

        $this->pdo->method('perform')->willReturn($stmt);
        $this->pdo->method('fetchOne')->willReturn($updated);

        $result = $this->service->update(1, ['name' => 'Updated Name']);

        $this->assertSame('Updated Name', $result['name']);
    }

    public function testDeleteReturnsTrueWhenRowAffected(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('rowCount')->willReturn(1);
        $this->pdo->method('perform')->willReturn($stmt);

        $result = $this->service->delete(1);

        $this->assertTrue($result);
    }

    public function testDeleteReturnsFalseWhenNoRowAffected(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('rowCount')->willReturn(0);
        $this->pdo->method('perform')->willReturn($stmt);

        $result = $this->service->delete(999);

        $this->assertFalse($result);
    }

    public function testVerifyPasswordReturnsTrueForCorrectPassword(): void
    {
        $hash = password_hash('secret', PASSWORD_BCRYPT);
        $this->assertTrue($this->service->verifyPassword('secret', $hash));
    }

    public function testVerifyPasswordReturnsFalseForWrongPassword(): void
    {
        $hash = password_hash('secret', PASSWORD_BCRYPT);
        $this->assertFalse($this->service->verifyPassword('wrong', $hash));
    }
}
