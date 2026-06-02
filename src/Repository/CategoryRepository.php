<?php
declare(strict_types=1);

namespace Himatsudo\Repository;

use Aura\Sql\ExtendedPdoInterface;

final class CategoryRepository
{
    public function __construct(private readonly ExtendedPdoInterface $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findAll(): array
    {
        return $this->pdo->fetchAll(
            'SELECT id, name, slug, type, sort_order, created_at, updated_at FROM categories ORDER BY sort_order ASC, id ASC'
        );
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $row = $this->pdo->fetchOne(
            'SELECT id, name, slug, type, sort_order, created_at, updated_at FROM categories WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
        return $row ?: null;
    }

    public function create(string $name, string $slug, string $type = 'custom', int $sortOrder = 0): int
    {
        $this->pdo->perform(
            'INSERT INTO categories (name, slug, type, sort_order) VALUES (:name, :slug, :type, :sort_order)',
            ['name' => $name, 'slug' => $slug, 'type' => $type, 'sort_order' => $sortOrder]
        );
        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        $sets = [];
        $bind = ['id' => $id];
        foreach (['name', 'slug', 'type', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "{$field} = :{$field}";
                $bind[$field] = $data[$field];
            }
        }
        if (empty($sets)) {
            return false;
        }
        $sql = 'UPDATE categories SET ' . implode(', ', $sets) . ' WHERE id = :id';
        return (bool) $this->pdo->perform($sql, $bind)->rowCount();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->pdo->perform('DELETE FROM categories WHERE id = :id', ['id' => $id])->rowCount();
    }
}
