<?php

declare(strict_types=1);

namespace Himatsudo\Service;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Interfaces\CategoryInterface;

final class CategoryService implements CategoryInterface
{
    use SqlFileTrait;

    public function __construct(private readonly ExtendedPdoInterface $pdo)
    {
    }

    public function getAll(): array
    {
        return $this->pdo->fetchAll($this->sql('categories/get_all.sql'));
    }

    public function getById(int $id): ?array
    {
        $row = $this->pdo->fetchOne($this->sql('categories/get_by_id.sql'), ['id' => $id]);
        return $row ?: null;
    }

    public function getByType(string $type): ?array
    {
        foreach ($this->getAll() as $cat) {
            if (($cat['type'] ?? '') === $type) {
                return $cat;
            }
        }
        return null;
    }

    public function getBySlug(string $slug): ?array
    {
        $row = $this->pdo->fetchOne($this->sql('categories/get_by_slug.sql'), ['slug' => $slug]);
        return $row ?: null;
    }

    public function create(string $name, string $slug, string $type = 'custom', int $sortOrder = 0): array
    {
        $this->pdo->perform(
            'INSERT INTO categories (name, slug, type, sort_order) VALUES (:name, :slug, :type, :sort_order)',
            ['name' => $name, 'slug' => $slug, 'type' => $type, 'sort_order' => $sortOrder]
        );
        return $this->getById((int) $this->pdo->lastInsertId()) ?? [];
    }

    public function update(int $id, array $data): ?array
    {
        $sets = [];
        $bind = ['id' => $id];
        foreach (['name', 'slug', 'type', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]       = "{$field} = :{$field}";
                $bind[$field] = $data[$field];
            }
        }
        if (!empty($sets)) {
            $this->pdo->perform('UPDATE categories SET ' . implode(', ', $sets) . ' WHERE id = :id', $bind);
        }
        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->pdo->perform($this->sql('categories/delete.sql'), ['id' => $id])->rowCount();
    }
}
