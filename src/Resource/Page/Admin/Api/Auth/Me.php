<?php

declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api\Auth;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Interfaces\UserInterface;

class Me extends ResourceObject
{
    public function __construct(private readonly UserInterface $userService)
    {
    }

    #[RequireAuth]
    public function onGet(): static
    {
        $uid  = (int) ($_REQUEST['_auth_uid'] ?? 0);
        $user = $this->userService->getById($uid);
        if ($user === null) {
            $this->code = 401;
            $this->body = ['error' => 'User not found'];
            return $this;
        }
        $this->body = $user;
        return $this;
    }
}
