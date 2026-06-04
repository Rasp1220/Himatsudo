SELECT id, title, slug, eye_catch_image, youtube_thumbnail
FROM articles
WHERE status = 'published'
  AND published_at IS NOT NULL
  AND (
      published_at > :published_at
      OR (published_at = :published_at AND id > :current_id)
  )
ORDER BY published_at ASC, id ASC
LIMIT 1
