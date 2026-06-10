<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Auth\AuthContext;
use Himatsudo\Domain\User as UserEntity;
use Himatsudo\Interfaces\UserInterface as UserServiceInterface;

class Users extends ResourceObject
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly AuthContext          $authContext,
    ) {
    }

    #[RequireAuth]
    public function onGet(int $page = 1, int $per_page = 20): static
    {
        if (!$this->isAdmin()) {
            return $this;
        }
        $result          = $this->userService->getList($page, $per_page);
        $result['items'] = array_map(static fn (UserEntity $u) => $u->toArray(), $result['items']);
        $this->body      = $result;
        return $this;
    }

    #[RequireAuth]
    public function onPost(string $name, string $email, string $password, string $role = 'editor'): static
    {
        if (!$this->isAdmin()) {
            return $this;
        }
        if (!in_array($role, ['admin', 'editor'], true)) {
            $this->code = 422;
            $this->body = ['error' => 'Invalid role'];
            return $this;
        }
        $this->code = 201;
        $this->body = $this->userService->create($name, $email, $password, $role)->toArray();
        return $this;
    }

    private function isAdmin(): bool
    {
        if (!$this->authContext->isAdmin()) {
            $this->code = 403;
            $this->body = ['error' => 'Forbidden'];
            return false;
        }
        return true;
    }
}
