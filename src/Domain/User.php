<?php

declare(strict_types=1);

namespace Himatsudo\Domain;

final readonly class User
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $email,
        public string $role,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id:        (int)    ($row['id'] ?? 0),
            name:      (string) ($row['name'] ?? ''),
            email:     (string) ($row['email'] ?? ''),
            role:      (string) ($row['role'] ?? 'editor'),
            createdAt: (string) ($row['created_at'] ?? ''),
            updatedAt: (string) ($row['updated_at'] ?? ''),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => $this->role,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
