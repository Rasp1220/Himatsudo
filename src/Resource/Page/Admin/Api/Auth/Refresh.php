<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api\Auth;

use BEAR\Resource\ResourceObject;
use DateTimeImmutable;
use Himatsudo\Auth\JwtService;
use Himatsudo\Repository\RefreshTokenRepository;
use Himatsudo\Repository\UserRepository;
use Throwable;

class Refresh extends ResourceObject
{
    public function __construct(
        private readonly JwtService             $jwtService,
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly UserRepository         $userRepository
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

        $record = $this->refreshTokenRepository->findValid($refresh_token);
        if ($record === null) {
            $this->code = 401;
            $this->body = ['error' => 'Refresh token not found or expired'];
            return $this;
        }

        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            $this->code = 401;
            $this->body = ['error' => 'User not found'];
            return $this;
        }

        // Rotate tokens
        $this->refreshTokenRepository->delete($refresh_token);
        $newRefreshToken = $this->jwtService->issueRefreshToken($userId);
        $expiresAt       = (new DateTimeImmutable())->modify('+30 days')->format('Y-m-d H:i:s');
        $this->refreshTokenRepository->save($userId, $newRefreshToken, $expiresAt);

        $this->body = [
            'access_token'  => $this->jwtService->issueAccessToken($userId, (string) $user['role']),
            'refresh_token' => $newRefreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => 3600,
        ];

        return $this;
    }
}
