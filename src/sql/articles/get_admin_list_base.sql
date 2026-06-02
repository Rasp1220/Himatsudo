-- ベースクエリ。Service 側で WHERE / ORDER BY / LIMIT を動的付加する。
-- params: limit, offset [, status, category_id, keyword]
SELECT a.id, a.title, a.slug, a.excerpt, a.eye_catch_image, a.status,
       a.youtube_thumbnail, a.published_at, a.created_at, a.updated_at,
       c.id AS category_id, c.name AS category_name, c.type AS category_type,
       u.id AS author_id, u.name AS author_name
FROM articles a
LEFT JOIN categories c ON c.id = a.category_id
LEFT JOIN users u      ON u.id = a.author_id
