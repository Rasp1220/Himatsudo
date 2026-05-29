<?php
declare(strict_types=1);

namespace Himatsudo\Api\Resource\App\Auth;

use BEAR\Resource\ResourceObject;
use DateTimeImmutable;
use Himatsudo\Api\Auth\JwtService;
use Himatsudo\Api\Repository\RefreshTokenRepository;
use Himatsudo\Api\Repository\UserRepository;

class Login extends ResourceObject
{
    public function __construct(
        private readonly UserRepository         $userRepository,
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly JwtService             $jwtService
    ) {
    }

    public function onPost(string $email, string $password): static
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user === null || !$this->userRepository->verifyPassword($password, (string) $user['password'])) {
            $this->code = 401;
            $this->body = ['error' => 'Invalid credentials'];
            return $this;
        }

        $userId       = (int) $user['id'];
        $role         = (string) $user['role'];
        $accessToken  = $this->jwtService->issueAccessToken($userId, $role);
        $refreshToken = $this->jwtService->issueRefreshToken($userId);

        $expiresAt = (new DateTimeImmutable())->modify('+30 days')->format('Y-m-d H:i:s');
        $this->refreshTokenRepository->save($userId, $refreshToken, $expiresAt);

        $this->code = 200;
        $this->body = [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => 3600,
            'user'          => [
                'id'    => $userId,
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $role,
            ],
        ];

        return $this;
    }
}
