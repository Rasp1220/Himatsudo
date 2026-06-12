<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api;

use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Resource\Page\Admin\Api\Users;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
{
    private UserInterface&MockObject $userService;
    private Users $resource;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserInterface::class);
        $this->resource    = new Users($this->userService);
    }

    protected function tearDown(): void
    {
        unset($_REQUEST['_auth_role']);
    }

    public function testOnGetReturns403WhenNotAdmin(): void
    {
        $_REQUEST['_auth_role'] = 'editor';

        $result = $this->resource->onGet();

        $this->assertSame(403, $result->code);
    }

    public function testOnGetReturnsUserListWhenAdmin(): void
    {
        $_REQUEST['_auth_role'] = 'admin';
        $list                   = ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 20, 'last_page' => 1];
        $this->userService->method('getList')->willReturn($list);

        $result = $this->resource->onGet();

        $this->assertSame(200, $result->code);
        $this->assertSame($list, $result->body);
    }

    public function testOnPostReturns403WhenNotAdmin(): void
    {
        $_REQUEST['_auth_role'] = 'editor';

        $result = $this->resource->onPost('Alice', 'alice@example.com', 'password', 'editor');

        $this->assertSame(403, $result->code);
    }

    public function testOnPostReturns422ForInvalidRole(): void
    {
        $_REQUEST['_auth_role'] = 'admin';

        $result = $this->resource->onPost('Alice', 'alice@example.com', 'password', 'superuser');

        $this->assertSame(422, $result->code);
    }

    public function testOnPostReturns201WithNewUserWhenValid(): void
    {
        $_REQUEST['_auth_role'] = 'admin';
        $newUser                = ['id' => 5, 'name' => 'Alice', 'email' => 'alice@example.com', 'role' => 'editor'];
        $this->userService->method('create')->willReturn($newUser);

        $result = $this->resource->onPost('Alice', 'alice@example.com', 'password', 'editor');

        $this->assertSame(201, $result->code);
        $this->assertSame($newUser, $result->body);
    }
}
