<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api\Auth;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Auth\AuthContext;
use Himatsudo\Interfaces\UserInterface;

class Me extends ResourceObject
{
    public function __construct(
        private readonly UserInterface $userService,
        private readonly AuthContext   $authContext,
    ) {
    }

    #[RequireAuth]
    public function onGet(): static
    {
        $user = $this->userService->getById($this->authContext->uid());
        if ($user === null) {
            $this->code = 401;
            $this->body = ['error' => 'User not found'];
            return $this;
        }
        $this->body = $user->toArray();
        return $this;
    }
}
