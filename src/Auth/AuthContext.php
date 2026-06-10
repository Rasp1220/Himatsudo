<?php
declare(strict_types=1);

namespace Himatsudo\Auth;

/**
 * Holds the authenticated user's claims for the current request.
 * Populated by AuthInterceptor after JWT validation; request-scoped
 * (one instance per request via singleton binding).
 */
final class AuthContext
{
    private int $uid = 0;
    private string $role = '';

    public function authenticate(int $uid, string $role): void
    {
        $this->uid  = $uid;
        $this->role = $role;
    }

    public function uid(): int
    {
        return $this->uid;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
