<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Domain;

use Himatsudo\Domain\Category;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    public function testFromArrayWithFullDataReturnsCorrectProperties(): void
    {
        $row = [
            'id'         => 4,
            'name'       => 'Technology',
            'slug'       => 'technology',
            'type'       => 'blog',
            'sort_order' => 2,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-06-01 00:00:00',
        ];

        $category = Category::fromArray($row);

        $this->assertSame(4, $category->id);
        $this->assertSame('Technology', $category->name);
        $this->assertSame('technology', $category->slug);
        $this->assertSame('blog', $category->type);
        $this->assertSame(2, $category->sortOrder);
        $this->assertSame('2024-01-01 00:00:00', $category->createdAt);
        $this->assertSame('2024-06-01 00:00:00', $category->updatedAt);
    }

    public function testFromArrayWithEmptyArrayUsesDefaults(): void
    {
        $category = Category::fromArray([]);

        $this->assertSame(0, $category->id);
        $this->assertSame('', $category->name);
        $this->assertSame('', $category->slug);
        $this->assertSame('custom', $category->type);
        $this->assertSame(0, $category->sortOrder);
        $this->assertSame('', $category->createdAt);
        $this->assertSame('', $category->updatedAt);
    }

    public function testToArrayReturnsSortOrderKey(): void
    {
        $row = [
            'id'         => 1,
            'name'       => 'News',
            'slug'       => 'news',
            'type'       => 'normal',
            'sort_order' => 5,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];

        $category = Category::fromArray($row);
        $arr      = $category->toArray();

        $this->assertArrayHasKey('sort_order', $arr);
        $this->assertArrayNotHasKey('sortOrder', $arr);
        $this->assertSame(5, $arr['sort_order']);
        $this->assertSame('News', $arr['name']);
        $this->assertSame('news', $arr['slug']);
        $this->assertSame('normal', $arr['type']);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
    }
}
