<?php
declare(strict_types=1);

namespace Himatsudo\Api\Resource\App\Auth;

use BEAR\Resource\ResourceObject;
use Himatsudo\Api\Annotation\RequireAuth;
use Himatsudo\Api\Repository\RefreshTokenRepository;

class Logout extends ResourceObject
{
    public function __construct(private readonly RefreshTokenRepository $refreshTokenRepository)
    {
    }

    #[RequireAuth]
    public function onPost(?string $refresh_token = null): static
    {
        if ($refresh_token !== null) {
            $this->refreshTokenRepository->delete($refresh_token);
        }
        $this->code = 204;
        $this->body = null;
        return $this;
    }
}
