-- seed: 002 default categories
INSERT INTO categories (name, slug, type, sort_order) VALUES
    ('通常記事', 'normal',  'normal',  1),
    ('ブログ',   'blog',    'blog',    2),
    ('YouTube',  'youtube', 'youtube', 3)
ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type), sort_order = VALUES(sort_order);
