<?php
declare(strict_types=1);

namespace Himatsudo\Interfaces;

use Himatsudo\Domain\User;

interface UserInterface
{
    /**
     * @return array{items: list<User>, total: int, page: int, per_page: int, last_page: int}
     */
    public function getList(int $page = 1, int $perPage = 20): array;

    public function getById(int $id): ?User;

    /** メールアドレスとパスワードを検証し、一致すればユーザーを返す（ハッシュは外部に出さない） */
    public function verifyCredentials(string $email, string $password): ?User;

    public function create(string $name, string $email, string $password, string $role = 'editor'): User;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ?User;

    public function delete(int $id): bool;
}
