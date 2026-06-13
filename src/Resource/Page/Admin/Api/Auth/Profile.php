<?php

declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api\Auth;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Interfaces\UserInterface;

class Profile extends ResourceObject
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

    #[RequireAuth]
    public function onPut(?string $name = null, ?string $email = null, ?string $password = null, ?string $avatar = null, ?string $bio = null, ?string $instagram_url = null, ?string $twitter_url = null, ?string $tiktok_url = null): static
    {
        // プロフィール設定はログイン中の本人のみ。対象は常に認証ユーザー自身。
        $uid = (int) ($_REQUEST['_auth_uid'] ?? 0);
        if ($this->userService->getById($uid) === null) {
            $this->code = 401;
            $this->body = ['error' => 'User not found'];
            return $this;
        }

        // role は本人からは変更させない（権限昇格防止）
        $data = array_filter(
            compact('name', 'email', 'password', 'avatar', 'bio', 'instagram_url', 'twitter_url', 'tiktok_url'),
            fn ($v) => $v !== null
        );

        $this->body = $this->userService->update($uid, $data);
        return $this;
    }
}
