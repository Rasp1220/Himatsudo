<?php
declare(strict_types=1);

namespace Himatsudo\Api\Resource\App;

use BEAR\Resource\ResourceObject;
use Himatsudo\Api\Annotation\RequireAuth;
use Himatsudo\Api\Repository\UserRepository;

class User extends ResourceObject
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    #[RequireAuth]
    public function onGet(int $id): static
    {
        if (($_REQUEST['_auth_role'] ?? '') !== 'admin') {
            $this->code = 403;
            $this->body = ['error' => 'Forbidden'];
            return $this;
        }
        $user = $this->userRepository->findById($id);
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
        if (($_REQUEST['_auth_role'] ?? '') !== 'admin') {
            $this->code = 403;
            $this->body = ['error' => 'Forbidden'];
            return $this;
        }
        if ($this->userRepository->findById($id) === null) {
            $this->code = 404;
            $this->body = ['error' => 'User not found'];
            return $this;
        }
        $data = array_filter(compact('name', 'email', 'password', 'role'), fn($v) => $v !== null);
        if ($role !== null && !in_array($role, ['admin', 'editor'], true)) {
            $this->code = 422;
            $this->body = ['error' => 'Invalid role'];
            return $this;
        }
        $this->userRepository->update($id, $data);
        $this->body = $this->userRepository->findById($id);
        return $this;
    }

    #[RequireAuth]
    public function onDelete(int $id): static
    {
        if (($_REQUEST['_auth_role'] ?? '') !== 'admin') {
            $this->code = 403;
            $this->body = ['error' => 'Forbidden'];
            return $this;
        }
        if ($this->userRepository->findById($id) === null) {
            $this->code = 404;
            $this->body = ['error' => 'User not found'];
            return $this;
        }
        $this->userRepository->delete($id);
        $this->code = 204;
        $this->body = null;
        return $this;
    }
}
