<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api\Auth;

use BEAR\Resource\ResourceObject;
use DateTimeImmutable;
use Himatsudo\Auth\JwtService;
use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Service\RefreshTokenService;

class Login extends ResourceObject
{
    public function __construct(
        private readonly UserInterface        $userService,
        private readonly RefreshTokenService  $refreshTokenService,
        private readonly JwtService           $jwtService
    ) {
    }

    public function onPost(string $email, string $password): static
    {
        $user = $this->userService->verifyCredentials($email, $password);

        if ($user === null) {
            $this->code = 401;
            $this->body = ['error' => 'Invalid credentials'];
            return $this;
        }

        $accessToken  = $this->jwtService->issueAccessToken($user->id, $user->role);
        $refreshToken = $this->jwtService->issueRefreshToken($user->id);

        $expiresAt = (new DateTimeImmutable())->modify('+30 days')->format('Y-m-d H:i:s');
        $this->refreshTokenService->save($user->id, $refreshToken, $expiresAt);

        $this->code = 200;
        $this->body = [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => 3600,
            'user'          => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ];

        return $this;
    }
}
