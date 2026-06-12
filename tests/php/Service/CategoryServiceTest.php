<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Service;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Service\CategoryService;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * mirrors src/Service/CategoryService.php
 */
class CategoryServiceTest extends TestCase
{
    private ExtendedPdoInterface&MockObject $pdo;
    private CategoryService $service;

    protected function setUp(): void
    {
        $this->pdo     = $this->createMock(ExtendedPdoInterface::class);
        $this->service = new CategoryService($this->pdo);
    }

    private function makeCategory(int $id = 1, string $type = 'normal'): array
    {
        return [
            'id'         => $id,
            'name'       => 'Test Category',
            'slug'       => 'test-category',
            'type'       => $type,
            'sort_order' => 0,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];
    }

    public function testGetAllReturnsArray(): void
    {
        $cats = [$this->makeCategory(1), $this->makeCategory(2, 'blog')];
        $this->pdo->method('fetchAll')->willReturn($cats);

        $result = $this->service->getAll();

        $this->assertCount(2, $result);
    }

    public function testGetByIdReturnsCategory(): void
    {
        $this->pdo->method('fetchOne')->willReturn($this->makeCategory(3));

        $result = $this->service->getById(3);

        $this->assertNotNull($result);
        $this->assertSame(3, $result['id']);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $this->pdo->method('fetchOne')->willReturn(false);

        $result = $this->service->getById(999);

        $this->assertNull($result);
    }

    public function testGetByTypeReturnsMatchingCategory(): void
    {
        $cats = [
            $this->makeCategory(1, 'normal'),
            $this->makeCategory(2, 'blog'),
            $this->makeCategory(3, 'youtube'),
        ];
        $this->pdo->method('fetchAll')->willReturn($cats);

        $result = $this->service->getByType('blog');

        $this->assertNotNull($result);
        $this->assertSame('blog', $result['type']);
    }

    public function testGetByTypeReturnsNullWhenTypeNotFound(): void
    {
        $this->pdo->method('fetchAll')->willReturn([$this->makeCategory(1, 'normal')]);

        $result = $this->service->getByType('youtube');

        $this->assertNull($result);
    }

    public function testGetBySlugReturnsCategory(): void
    {
        $this->pdo->method('fetchOne')->willReturn($this->makeCategory());

        $result = $this->service->getBySlug('test-category');

        $this->assertNotNull($result);
        $this->assertSame('test-category', $result['slug']);
    }

    public function testCreateReturnsNewCategory(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $this->pdo->method('perform')->willReturn($stmt);
        $this->pdo->method('lastInsertId')->willReturn('5');
        $this->pdo->method('fetchOne')->willReturn($this->makeCategory(5));

        $result = $this->service->create('New Category', 'new-category');

        $this->assertSame(5, $result['id']);
    }

    public function testUpdateReturnsUpdatedCategory(): void
    {
        $stmt    = $this->createMock(PDOStatement::class);
        $updated = array_merge($this->makeCategory(), ['name' => 'Renamed']);

        $this->pdo->method('perform')->willReturn($stmt);
        $this->pdo->method('fetchOne')->willReturn($updated);

        $result = $this->service->update(1, ['name' => 'Renamed']);

        $this->assertSame('Renamed', $result['name']);
    }

    public function testUpdateWithEmptyDataSkipsQuery(): void
    {
        $cat = $this->makeCategory();
        $this->pdo->expects($this->never())->method('perform');
        $this->pdo->method('fetchOne')->willReturn($cat);

        $result = $this->service->update(1, []);

        $this->assertSame($cat, $result);
    }

    public function testDeleteReturnsTrueWhenDeleted(): void
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
