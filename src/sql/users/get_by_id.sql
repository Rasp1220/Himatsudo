SELECT id, name, email, role, avatar, bio, instagram_url, twitter_url, tiktok_url, created_at, updated_at
FROM users
WHERE id = :id
LIMIT 1
