<?php
declare(strict_types=1);

namespace Himatsudo\Api\Resource\App\Auth;

use BEAR\Resource\ResourceObject;
use Himatsudo\Api\Annotation\RequireAuth;
use Himatsudo\Api\Repository\UserRepository;

class Me extends ResourceObject
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    #[RequireAuth]
    public function onGet(): static
    {
        $uid  = (int) ($_REQUEST['_auth_uid'] ?? 0);
        $user = $this->userRepository->findById($uid);
        if ($user === null) {
            $this->code = 401;
            $this->body = ['error' => 'User not found'];
            return $this;
        }
        $this->body = $user;
        return $this;
    }
}
