<?php

declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Interfaces\UserInterface as UserServiceInterface;

class User extends ResourceObject
{
    public function __construct(private readonly UserServiceInterface $userService)
    {
    }

    #[RequireAuth]
    public function onGet(int $id): static
    {
        if (!$this->isAdmin()) {
            return $this;
        }
        $user = $this->userService->getById($id);
        if ($user === null) {
            $this->code = 404;
            $this->body = ['error' => 'User not found'];
            return $this;
        }
        $this->body = $user;
        return $this;
    }

    #[RequireAuth]
    public function onPut(int $id, ?string $name = null, ?string $email = null, ?string $password = null, ?string $role = null): static
    {
        if (!$this->isAdmin()) {
            return $this;
        }
        if ($this->userService->getById($id) === null) {
            $this->code = 404;
            $this->body = ['error' => 'User not found'];
            return $this;
        }
        $data = array_filter(compact('name', 'email', 'password', 'role'), fn ($v) => $v !== null);
        if ($role !== null && !in_array($role, ['admin', 'editor'], true)) {
            $this->code = 422;
            $this->body = ['error' => 'Invalid role'];
            return $this;
        }
        $this->body = $this->userService->update($id, $data);
        return $this;
    }

    #[RequireAuth]
    public function onDelete(int $id): static
    {
        if (!$this->isAdmin()) {
            return $this;
        }
        if ($this->userService->getById($id) === null) {
            $this->code = 404;
            $this->body = ['error' => 'User not found'];
            return $this;
        }
        $this->userService->delete($id);
        $this->code = 204;
        $this->body = null;
        return $this;
    }

    private function isAdmin(): bool
    {
        if (($_REQUEST['_auth_role'] ?? '') !== 'admin') {
            $this->code = 403;
            $this->body = ['error' => 'Forbidden'];
            return false;
        }
        return true;
    }
}
