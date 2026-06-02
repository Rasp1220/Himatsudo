<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api\Auth;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Service\RefreshTokenService;

class Logout extends ResourceObject
{
    public function __construct(private readonly RefreshTokenService $refreshTokenService)
    {
    }

    #[RequireAuth]
    public function onPost(?string $refresh_token = null): static
    {
        if ($refresh_token !== null) {
            $this->refreshTokenService->delete($refresh_token);
        }
        $this->code = 204;
        $this->body = null;
        return $this;
    }
}
