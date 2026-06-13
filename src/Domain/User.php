<?php

declare(strict_types=1);

namespace Himatsudo\Domain;

final readonly class User
{
    public function __construct(
        public int     $id,
        public string  $name,
        public string  $email,
        public string  $role,
        public ?string $avatar,
        public ?string $bio,
        public ?string $instagramUrl,
        public ?string $twitterUrl,
        public ?string $tiktokUrl,
        public string  $createdAt,
        public string  $updatedAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id:           (int)    ($row['id'] ?? 0),
            name:         (string) ($row['name'] ?? ''),
            email:        (string) ($row['email'] ?? ''),
            role:         (string) ($row['role'] ?? 'editor'),
            avatar:       isset($row['avatar']) ? (string) $row['avatar'] : null,
            bio:          isset($row['bio']) ? (string) $row['bio'] : null,
            instagramUrl: isset($row['instagram_url']) ? (string) $row['instagram_url'] : null,
            twitterUrl:   isset($row['twitter_url']) ? (string) $row['twitter_url'] : null,
            tiktokUrl:    isset($row['tiktok_url']) ? (string) $row['tiktok_url'] : null,
            createdAt:    (string) ($row['created_at'] ?? ''),
            updatedAt:    (string) ($row['updated_at'] ?? ''),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'role'          => $this->role,
            'avatar'        => $this->avatar,
            'bio'           => $this->bio,
            'instagram_url' => $this->instagramUrl,
            'twitter_url'   => $this->twitterUrl,
            'tiktok_url'    => $this->tiktokUrl,
            'created_at'    => $this->createdAt,
            'updated_at'    => $this->updatedAt,
        ];
    }
}
