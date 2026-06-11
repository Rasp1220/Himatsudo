<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api;

use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Resource\Page\Admin\Api\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private UserInterface&MockObject $userService;
    private User $resource;

    protected function setUp(): void
    {
        $this->userService      = $this->createMock(UserInterface::class);
        $this->resource         = new User($this->userService);
        $_REQUEST['_auth_role'] = 'editor';
    }

    protected function tearDown(): void
    {
        unset($_REQUEST['_auth_role']);
    }

    private function makeUser(int $id = 1): array
    {
        return ['id' => $id, 'name' => 'Alice', 'email' => 'alice@example.com', 'role' => 'admin'];
    }

    public function testOnGetReturns403WhenNotAdmin(): void
    {
        $_REQUEST['_auth_role'] = 'editor';

        $result = $this->resource->onGet(1);

        $this->assertSame(403, $result->code);
    }

    public function testOnGetReturns404WhenAdminButUserNotFound(): void
    {
        $_REQUEST['_auth_role'] = 'admin';
        $this->userService->method('getById')->willReturn(null);

        $result = $this->resource->onGet(999);

        $this->assertSame(404, $result->code);
    }

    public function testOnGetReturns200WithUserBodyWhenAdminAndFound(): void
    {
        $_REQUEST['_auth_role'] = 'admin';
        $user                   = $this->makeUser();
        $this->userService->method('getById')->willReturn($user);

        $result = $this->resource->onGet(1);

        $this->assertSame(200, $result->code);
        $this->assertSame($user, $result->body);
    }

    public function testOnPutReturns422ForInvalidRole(): void
    {
        $_REQUEST['_auth_role'] = 'admin';
        $this->userService->method('getById')->willReturn($this->makeUser());

        $result = $this->resource->onPut(1, null, null, null, 'superuser');

        $this->assertSame(422, $result->code);
    }

    public function testOnDeleteReturns204WhenAdminAndFound(): void
    {
        $_REQUEST['_auth_role'] = 'admin';
        $this->userService->method('getById')->willReturn($this->makeUser());
        $this->userService->method('delete')->willReturn(true);

        $result = $this->resource->onDelete(1);

        $this->assertSame(204, $result->code);
    }
}
