<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api\Auth;

use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Resource\Page\Admin\Api\Auth\Profile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    private UserInterface&MockObject $userService;
    private Profile $resource;

    protected function setUp(): void
    {
        $this->userService = $this->createMock(UserInterface::class);
        $this->resource    = new Profile($this->userService);
    }

    protected function tearDown(): void
    {
        unset($_REQUEST['_auth_uid']);
    }

    public function testOnGetReturnsAuthenticatedUser(): void
    {
        $_REQUEST['_auth_uid'] = 3;
        $user                  = ['id' => 3, 'name' => 'Bob', 'email' => 'bob@example.com', 'role' => 'editor'];
        $this->userService->method('getById')->willReturn($user);

        $result = $this->resource->onGet();

        $this->assertSame(200, $result->code);
        $this->assertSame($user, $result->body);
    }

    public function testOnPutUpdatesOnlyAuthenticatedUser(): void
    {
        $_REQUEST['_auth_uid'] = 3;
        $user                  = ['id' => 3, 'name' => 'Bob', 'email' => 'bob@example.com', 'role' => 'editor'];
        $this->userService->method('getById')->willReturn($user);

        // 対象は常にトークンの uid（3）。role は渡されても更新対象に含まれない。
        $this->userService->expects($this->once())
            ->method('update')
            ->with(3, ['name' => 'Bobby', 'bio' => 'hello'])
            ->willReturn(array_merge($user, ['name' => 'Bobby', 'bio' => 'hello']));

        $result = $this->resource->onPut(name: 'Bobby', bio: 'hello');

        $this->assertSame('Bobby', $result->body['name']);
    }

    public function testOnPutReturns401WhenUserMissing(): void
    {
        $_REQUEST['_auth_uid'] = 99;
        $this->userService->method('getById')->willReturn(null);

        $result = $this->resource->onPut(name: 'X');

        $this->assertSame(401, $result->code);
    }

    public function testOnPutForwardsSnsFields(): void
    {
        $_REQUEST['_auth_uid'] = 3;
        $user                  = ['id' => 3, 'name' => 'Bob', 'email' => 'bob@example.com', 'role' => 'editor'];
        $this->userService->method('getById')->willReturn($user);

        $this->userService->expects($this->once())
            ->method('update')
            ->with(3, [
                'instagram_url' => 'https://instagram.com/bob',
                'twitter_url'   => 'https://x.com/bob',
                'tiktok_url'    => 'https://tiktok.com/@bob',
            ])
            ->willReturn(array_merge($user, [
                'instagram_url' => 'https://instagram.com/bob',
                'twitter_url'   => 'https://x.com/bob',
                'tiktok_url'    => 'https://tiktok.com/@bob',
            ]));

        $result = $this->resource->onPut(
            instagram_url: 'https://instagram.com/bob',
            twitter_url: 'https://x.com/bob',
            tiktok_url: 'https://tiktok.com/@bob',
        );

        $this->assertSame(200, $result->code);
        $this->assertSame('https://instagram.com/bob', $result->body['instagram_url']);
    }
}
