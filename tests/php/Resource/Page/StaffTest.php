<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page;

use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Resource\Page\Staff;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StaffTest extends TestCase
{
    private UserInterface&MockObject $userService;
    private CategoryInterface&MockObject $categoryService;

    protected function setUp(): void
    {
        $this->userService     = $this->createMock(UserInterface::class);
        $this->categoryService = $this->createMock(CategoryInterface::class);
    }

    public function testOnGetReturnsStaffListWithTemplate(): void
    {
        $staff = [
            ['id' => 1, 'name' => '管理者', 'avatar' => null, 'bio' => null],
            ['id' => 2, 'name' => '編集者', 'avatar' => '/img/a.png', 'bio' => 'hello'],
        ];
        $this->userService->method('getPublicList')->willReturn($staff);
        $this->categoryService->method('getAll')->willReturn([]);

        $page   = new Staff($this->userService, $this->categoryService);
        $result = $page->onGet();

        $this->assertSame('staff/index', $result->body['_template']);
        $this->assertSame($staff, $result->body['staff']);
        $this->assertSame('運営一覧', $result->body['page_title']);
    }
}
