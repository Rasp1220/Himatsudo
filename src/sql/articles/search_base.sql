-- ベースクエリ。Service 側で WHERE / ORDER BY / LIMIT を動的付加する。
-- params: keyword, limit, offset
SELECT a.id, a.title, a.slug, a.excerpt, a.eye_catch_image,
       a.youtube_thumbnail, a.published_at,
       c.id AS category_id, c.name AS category_name, c.slug AS category_slug, c.type AS category_type
FROM articles a
LEFT JOIN categories c ON c.id = a.category_id
