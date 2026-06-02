<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Repository\UserRepository;

class Users extends ResourceObject
{
    public function __construct(private readonly UserRepository $userRepository)
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
        $items = $this->userRepository->findAll($page, $per_page);
        $total = $this->userRepository->count();
        $this->body = [
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $per_page,
            'last_page' => (int) ceil($total / $per_page),
        ];
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
        $id = $this->userRepository->create($name, $email, $password, $role);
        $this->code = 201;
        $this->body = $this->userRepository->findById($id);
        return $this;
    }
}
