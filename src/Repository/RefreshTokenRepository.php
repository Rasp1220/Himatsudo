<?php
declare(strict_types=1);

namespace Himatsudo\Api\Repository;

use Aura\Sql\ExtendedPdoInterface;

final class RefreshTokenRepository
{
    public function __construct(private readonly ExtendedPdoInterface $pdo)
    {
    }

    public function save(int $userId, string $token, string $expiresAt): void
    {
        $this->pdo->perform(
            'INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)',
            ['user_id' => $userId, 'token' => $token, 'expires_at' => $expiresAt]
        );
    }

    /** @return array<string, mixed>|null */
    public function findValid(string $token): ?array
    {
        $row = $this->pdo->fetchOne(
            "SELECT id, user_id, token, expires_at FROM refresh_tokens
             WHERE token = :token AND expires_at > NOW() LIMIT 1",
            ['token' => $token]
        );
        return $row ?: null;
    }

    public function delete(string $token): void
    {
        $this->pdo->perform('DELETE FROM refresh_tokens WHERE token = :token', ['token' => $token]);
    }

    public function deleteExpired(): void
    {
        $this->pdo->perform('DELETE FROM refresh_tokens WHERE expires_at <= NOW()');
    }
}
