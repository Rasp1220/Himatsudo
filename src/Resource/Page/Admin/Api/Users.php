<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Contract\Service\UserServiceInterface;

class Users extends ResourceObject
{
    public function __construct(private readonly UserServiceInterface $userService)
    {
    }

    #[RequireAuth]
    public function onGet(int $page = 1, int $per_page = 20): static
    {
        if (($_REQUEST['_auth_role'] ?? '') !== 'admin') {
            $this->code = 403;
            $this->body = ['error' => 'Forbidden'];
            return $this;
        }
        $this->body = $this->userService->getList($page, $per_page);
        return $this;
    }

    #[RequireAuth]
    public function onPost(string $name, string $email, string $password, string $role = 'editor'): static
    {
        if (($_REQUEST['_auth_role'] ?? '') !== 'admin') {
            $this->code = 403;
            $this->body = ['error' => 'Forbidden'];
            return $this;
        }
        if (!in_array($role, ['admin', 'editor'], true)) {
            $this->code = 422;
            $this->body = ['error' => 'Invalid role'];
            return $this;
        }
        $this->code = 201;
        $this->body = $this->userService->create($name, $email, $password, $role);
        return $this;
    }
}
