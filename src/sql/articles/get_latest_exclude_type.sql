SELECT a.id, a.title, a.slug, a.excerpt, a.eye_catch_image, a.youtube_thumbnail,
       a.published_at,
       c.name AS category_name, c.slug AS category_slug, c.type AS category_type
FROM articles a
LEFT JOIN categories c ON c.id = a.category_id
WHERE a.status = 'published'
  AND (c.type IS NULL OR c.type != :exclude_type)
ORDER BY a.published_at DESC, a.created_at DESC
LIMIT :limit
