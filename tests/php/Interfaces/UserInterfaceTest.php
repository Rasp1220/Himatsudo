<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Interfaces;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Service\UserService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class UserInterfaceTest extends TestCase
{
    public function testUserServiceImplementsUserInterface(): void
    {
        $pdo     = $this->createMock(ExtendedPdoInterface::class);
        $service = new UserService($pdo);

        $this->assertInstanceOf(UserInterface::class, $service);
    }

    public function testUserInterfaceHasRequiredMethods(): void
    {
        $ref     = new ReflectionClass(UserInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $ref->getMethods());

        $required = ['getList', 'getById', 'getByEmail', 'create', 'update', 'delete', 'verifyPassword'];

        foreach ($required as $method) {
            $this->assertContains($method, $methods, "UserInterface must declare method '{$method}'");
        }
    }
}
