<?php
declare(strict_types=1);

namespace Himatsudo\Service;

use Himatsudo\Contract\Repository\UserRepositoryInterface;
use Himatsudo\Contract\Service\UserServiceInterface;

final class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function getList(int $page = 1, int $perPage = 20): array
    {
        $items = $this->userRepository->findAll($page, $perPage);
        $total = $this->userRepository->count();
        return [
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int) ceil($total / max(1, $perPage)),
        ];
    }

    public function getById(int $id): ?array
    {
        return $this->userRepository->findById($id);
    }

    public function getByEmail(string $email): ?array
    {
        return $this->userRepository->findByEmail($email);
    }

    public function create(string $name, string $email, string $password, string $role = 'editor'): array
    {
        $id = $this->userRepository->create($name, $email, $password, $role);
        return $this->userRepository->findById($id) ?? [];
    }

    public function update(int $id, array $data): ?array
    {
        $this->userRepository->update($id, $data);
        return $this->userRepository->findById($id);
    }

    public function delete(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return $this->userRepository->verifyPassword($plain, $hash);
    }
}
