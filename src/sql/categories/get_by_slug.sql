SELECT id, name, slug, type, sort_order, created_at, updated_at
FROM categories
WHERE slug = :slug
LIMIT 1
