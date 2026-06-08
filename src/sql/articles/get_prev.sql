SELECT a.id, a.title, a.slug, a.eye_catch_image, a.youtube_thumbnail, a.published_at,
       c.type AS category_type
FROM articles a
LEFT JOIN categories c ON c.id = a.category_id
WHERE a.status = 'published'
  AND (a.published_at < :published_at
       OR (a.published_at = :published_at AND a.id < :id))
ORDER BY a.published_at DESC, a.id DESC
LIMIT 1
