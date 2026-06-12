<?php

declare(strict_types=1);

namespace Himatsudo\Domain;

final readonly class Category
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $slug,
        public string $type,
        public int    $sortOrder,
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
            slug:      (string) ($row['slug'] ?? ''),
            type:      (string) ($row['type'] ?? 'custom'),
            sortOrder: (int)    ($row['sort_order'] ?? 0),
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
            'slug'       => $this->slug,
            'type'       => $this->type,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
