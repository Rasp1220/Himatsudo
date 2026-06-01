SELECT id, name, slug, type, sort_order, created_at, updated_at
FROM categories
ORDER BY sort_order ASC, id ASC
