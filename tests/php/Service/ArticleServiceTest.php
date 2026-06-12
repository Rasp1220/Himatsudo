<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Service;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Service\ArticleService;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * mirrors src/Service/ArticleService.php
 */
class ArticleServiceTest extends TestCase
{
    private ExtendedPdoInterface&MockObject $pdo;
    private ArticleService $service;

    protected function setUp(): void
    {
        $this->pdo     = $this->createMock(ExtendedPdoInterface::class);
        $this->service = new ArticleService($this->pdo);
    }

    private function makeArticle(int $id = 1, string $status = 'published'): array
    {
        return [
            'id'              => $id,
            'title'           => 'Test Article',
            'slug'            => 'test-article',
            'content'         => '<p>content</p>',
            'blocks'          => null,
            'excerpt'         => 'excerpt',
            'eye_catch_image' => null,
            'category_id'     => null,
            'category_name'   => null,
            'author_id'       => 1,
            'author_name'     => 'Admin',
            'status'          => $status,
            'published_at'    => $status === 'published' ? '2024-01-01 00:00:00' : null,
            'created_at'      => '2024-01-01 00:00:00',
            'updated_at'      => '2024-01-01 00:00:00',
        ];
    }

    public function testGetListReturnsPaginatedResult(): void
    {
        $this->pdo->method('fetchAll')->willReturn([$this->makeArticle()]);
        $this->pdo->method('fetchValue')->willReturn('1');

        $result = $this->service->getList();

        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('last_page', $result);
        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['last_page']);
    }

    public function testGetListLastPageCalculation(): void
    {
        $this->pdo->method('fetchAll')->willReturn([]);
        $this->pdo->method('fetchValue')->willReturn('31');

        $result = $this->service->getList(1, 15);

        $this->assertSame(3, $result['last_page']);
    }

    public function testGetAdminListFiltersByStatus(): void
    {
        $capturedSql = '';
        $this->pdo->method('fetchAll')
            ->willReturnCallback(function (string $sql) use (&$capturedSql) {
                $capturedSql = $sql;
                return [];
            });
        $this->pdo->method('fetchValue')->willReturn('0');

        $this->service->getAdminList(1, 20, null, 'draft');

        $this->assertStringContainsString(':status', $capturedSql);
    }

    public function testGetAdminListFiltersByKeyword(): void
    {
        $capturedSql = '';
        $this->pdo->method('fetchAll')
            ->willReturnCallback(function (string $sql) use (&$capturedSql) {
                $capturedSql = $sql;
                return [];
            });
        $this->pdo->method('fetchValue')->willReturn('0');

        $this->service->getAdminList(1, 20, null, null, 'hello');

        $this->assertStringContainsString(':keyword', $capturedSql);
    }

    public function testGetBySlugReturnsArticleWhenFound(): void
    {
        $this->pdo->method('fetchOne')->willReturn($this->makeArticle());

        $result = $this->service->getBySlug('test-article');

        $this->assertNotNull($result);
        $this->assertSame('test-article', $result['slug']);
    }

    public function testGetBySlugReturnsNullWhenNotFound(): void
    {
        $this->pdo->method('fetchOne')->willReturn(false);

        $result = $this->service->getBySlug('no-such-article');

        $this->assertNull($result);
    }

    public function testGetByIdReturnsArticle(): void
    {
        $this->pdo->method('fetchOne')->willReturn($this->makeArticle(7));

        $result = $this->service->getById(7);

        $this->assertNotNull($result);
        $this->assertSame(7, $result['id']);
    }

    public function testGetLatestReturnsArray(): void
    {
        $this->pdo->method('fetchAll')->willReturn([$this->makeArticle(), $this->makeArticle(2)]);

        $result = $this->service->getLatest(2);

        $this->assertCount(2, $result);
    }

    public function testCreateSetsPublishedAtWhenStatusIsPublished(): void
    {
        $capturedData = null;
        $stmt         = $this->createMock(PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('perform')
            ->willReturnCallback(function (string $sql, array $data) use ($stmt, &$capturedData) {
                $capturedData = $data;
                return $stmt;
            });
        $this->pdo->method('lastInsertId')->willReturn('1');
        $this->pdo->method('fetchOne')->willReturn($this->makeArticle());

        $this->service->create([
            'title'     => 'Article',
            'slug'      => 'article',
            'status'    => 'published',
            'author_id' => 1,
        ]);

        $this->assertArrayHasKey('published_at', $capturedData);
        $this->assertNotEmpty($capturedData['published_at']);
    }

    public function testCreateDoesNotOverwriteExistingPublishedAt(): void
    {
        $capturedData = null;
        $stmt         = $this->createMock(PDOStatement::class);

        $this->pdo->expects($this->once())
            ->method('perform')
            ->willReturnCallback(function (string $sql, array $data) use ($stmt, &$capturedData) {
                $capturedData = $data;
                return $stmt;
            });
        $this->pdo->method('lastInsertId')->willReturn('1');
        $this->pdo->method('fetchOne')->willReturn($this->makeArticle());

        $this->service->create([
            'title'        => 'Article',
            'slug'         => 'article',
            'status'       => 'published',
            'published_at' => '2020-01-01 00:00:00',
            'author_id'    => 1,
        ]);

        $this->assertSame('2020-01-01 00:00:00', $capturedData['published_at']);
    }

    public function testUpdateReturnsUpdatedArticle(): void
    {
        $stmt    = $this->createMock(PDOStatement::class);
        $updated = array_merge($this->makeArticle(), ['title' => 'Updated Title']);

        $this->pdo->method('perform')->willReturn($stmt);
        $this->pdo->method('fetchOne')->willReturn($updated);

        $result = $this->service->update(1, ['title' => 'Updated Title']);

        $this->assertSame('Updated Title', $result['title']);
    }

    public function testDeleteReturnsTrueOnSuccess(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('rowCount')->willReturn(1);
        $this->pdo->method('perform')->willReturn($stmt);

        $this->assertTrue($this->service->delete(1));
    }

    public function testDeleteReturnsFalseWhenNotFound(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('rowCount')->willReturn(0);
        $this->pdo->method('perform')->willReturn($stmt);

        $this->assertFalse($this->service->delete(999));
    }
}
