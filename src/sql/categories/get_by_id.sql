SELECT id, name, slug, type, sort_order, created_at, updated_at
FROM categories
WHERE id = :id
LIMIT 1
