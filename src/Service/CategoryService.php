<?php
declare(strict_types=1);

namespace Himatsudo\Service;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Interfaces\CategoryInterface;

final class CategoryService implements CategoryInterface
{
    public function __construct(private readonly ExtendedPdoInterface $pdo) {}

    public function getAll(): array
    {
        return $this->pdo->fetchAll(
            'SELECT id, name, slug, type, sort_order, created_at, updated_at FROM categories ORDER BY sort_order ASC, id ASC'
        );
    }

    public function getById(int $id): ?array
    {
        $row = $this->pdo->fetchOne(
            'SELECT id, name, slug, type, sort_order, created_at, updated_at FROM categories WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        return $row ?: null;
    }

    public function create(string $name, string $slug, string $type = 'custom', int $sortOrder = 0): array
    {
        $this->pdo->perform(
            'INSERT INTO categories (name, slug, type, sort_order) VALUES (:name, :slug, :type, :sort_order)',
            ['name' => $name, 'slug' => $slug, 'type' => $type, 'sort_order' => $sortOrder]
        );
        $id = (int) $this->pdo->lastInsertId();
        return $this->getById($id) ?? [];
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
        return (bool) $this->pdo->perform('DELETE FROM categories WHERE id = :id', ['id' => $id])->rowCount();
    }
}
