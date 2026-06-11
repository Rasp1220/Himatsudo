<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api\Auth;

use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Resource\Page\Admin\Api\Auth\Me;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MeTest extends TestCase
{
    private UserInterface&MockObject $userService;
    private Me $resource;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserInterface::class);
        $this->resource    = new Me($this->userService);
    }

    protected function tearDown(): void
    {
        unset($_REQUEST['_auth_uid']);
    }

    public function testOnGetReturns401WhenUserNotFound(): void
    {
        $_REQUEST['_auth_uid'] = 99;
        $this->userService->method('getById')->willReturn(null);

        $result = $this->resource->onGet();

        $this->assertSame(401, $result->code);
        $this->assertArrayHasKey('error', $result->body);
    }

    public function testOnGetReturns200WithUserBodyWhenUserFound(): void
    {
        $_REQUEST['_auth_uid'] = 1;
        $user                  = ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com', 'role' => 'admin'];
        $this->userService->method('getById')->willReturn($user);

        $result = $this->resource->onGet();

        $this->assertSame(200, $result->code);
        $this->assertSame($user, $result->body);
    }
}
