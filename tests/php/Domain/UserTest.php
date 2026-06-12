<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Domain;

use Himatsudo\Domain\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testFromArrayWithFullDataReturnsCorrectProperties(): void
    {
        $row = [
            'id'         => 10,
            'name'       => 'John Doe',
            'email'      => 'john@example.com',
            'role'       => 'admin',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-06-01 00:00:00',
        ];

        $user = User::fromArray($row);

        $this->assertSame(10, $user->id);
        $this->assertSame('John Doe', $user->name);
        $this->assertSame('john@example.com', $user->email);
        $this->assertSame('admin', $user->role);
        $this->assertSame('2024-01-01 00:00:00', $user->createdAt);
        $this->assertSame('2024-06-01 00:00:00', $user->updatedAt);
    }

    public function testFromArrayWithEmptyArrayUsesDefaults(): void
    {
        $user = User::fromArray([]);

        $this->assertSame(0, $user->id);
        $this->assertSame('', $user->name);
        $this->assertSame('', $user->email);
        $this->assertSame('editor', $user->role);
        $this->assertSame('', $user->createdAt);
        $this->assertSame('', $user->updatedAt);
    }

    public function testToArrayReturnsCorrectKeys(): void
    {
        $row = [
            'id'         => 3,
            'name'       => 'Jane',
            'email'      => 'jane@example.com',
            'role'       => 'editor',
            'created_at' => '2024-02-01 00:00:00',
            'updated_at' => '2024-02-01 00:00:00',
        ];

        $user = User::fromArray($row);
        $arr  = $user->toArray();

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('name', $arr);
        $this->assertArrayHasKey('email', $arr);
        $this->assertArrayHasKey('role', $arr);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
        $this->assertSame(3, $arr['id']);
        $this->assertSame('jane@example.com', $arr['email']);
        $this->assertSame('editor', $arr['role']);
    }
}
