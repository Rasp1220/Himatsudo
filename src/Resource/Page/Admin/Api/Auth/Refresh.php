<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api\Auth;

use BEAR\Resource\ResourceObject;
use DateTimeImmutable;
use Himatsudo\Auth\JwtService;
use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Service\RefreshTokenService;
use Throwable;

class Refresh extends ResourceObject
{
    public function __construct(
        private readonly JwtService           $jwtService,
        private readonly RefreshTokenService  $refreshTokenService,
        private readonly UserInterface        $userService
    ) {
    }

    public function onPost(string $refresh_token): static
    {
        try {
            $userId = $this->jwtService->validateRefreshToken($refresh_token);
        } catch (Throwable) {
            $this->code = 401;
            $this->body = ['error' => 'Invalid refresh token'];
            return $this;
        }

        $record = $this->refreshTokenService->findValid($refresh_token);
        if ($record === null) {
            $this->code = 401;
            $this->body = ['error' => 'Refresh token not found or expired'];
            return $this;
        }

        $user = $this->userService->getById($userId);
        if ($user === null) {
            $this->code = 401;
            $this->body = ['error' => 'User not found'];
            return $this;
        }

        $this->refreshTokenService->delete($refresh_token);
        $newRefreshToken = $this->jwtService->issueRefreshToken($userId);
        $expiresAt       = (new DateTimeImmutable())->modify('+30 days')->format('Y-m-d H:i:s');
        $this->refreshTokenService->save($userId, $newRefreshToken, $expiresAt);

        $this->body = [
            'access_token'  => $this->jwtService->issueAccessToken($userId, (string) $user['role']),
            'refresh_token' => $newRefreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => 3600,
        ];

        return $this;
    }
}
