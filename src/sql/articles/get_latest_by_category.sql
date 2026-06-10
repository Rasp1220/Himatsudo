SELECT a.id, a.title, a.slug, a.eye_catch_image, a.youtube_thumbnail,
       a.status, a.published_at, a.created_at
FROM articles a
WHERE a.status = 'published'
  AND a.category_id = :category_id
ORDER BY a.published_at DESC, a.created_at DESC
LIMIT :limit
