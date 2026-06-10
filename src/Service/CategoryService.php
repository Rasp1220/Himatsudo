<?php
declare(strict_types=1);

namespace Himatsudo\Service;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Domain\Category;
use Himatsudo\Interfaces\CategoryInterface;
use RuntimeException;

final class CategoryService implements CategoryInterface
{
    use SqlFileTrait;

    public function __construct(private readonly ExtendedPdoInterface $pdo) {}

    public function getAll(): array
    {
        return array_map(
            static fn (array $row) => Category::fromArray($row),
            $this->pdo->fetchAll($this->sql('categories/get_all.sql'))
        );
    }

    public function getById(int $id): ?Category
    {
        $row = $this->pdo->fetchOne($this->sql('categories/get_by_id.sql'), ['id' => $id]);
        return $row ? Category::fromArray($row) : null;
    }

    public function getByType(string $type): ?Category
    {
        foreach ($this->getAll() as $category) {
            if ($category->type === $type) {
                return $category;
            }
        }
        return null;
    }

    public function getBySlug(string $slug): ?Category
    {
        $row = $this->pdo->fetchOne($this->sql('categories/get_by_slug.sql'), ['slug' => $slug]);
        return $row ? Category::fromArray($row) : null;
    }

    public function create(string $name, string $slug, string $type = 'custom', int $sortOrder = 0): Category
    {
        $this->pdo->perform(
            'INSERT INTO categories (name, slug, type, sort_order) VALUES (:name, :slug, :type, :sort_order)',
            ['name' => $name, 'slug' => $slug, 'type' => $type, 'sort_order' => $sortOrder]
        );
        return $this->getById((int) $this->pdo->lastInsertId())
            ?? throw new RuntimeException('Failed to load created category');
    }

    public function update(int $id, array $data): ?Category
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
