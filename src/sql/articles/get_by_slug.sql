SELECT a.*,
       c.name AS category_name, c.slug AS category_slug, c.type AS category_type,
       u.name AS author_name
FROM articles a
LEFT JOIN categories c ON c.id = a.category_id
LEFT JOIN users u      ON u.id = a.author_id
WHERE a.slug = :slug
LIMIT 1
