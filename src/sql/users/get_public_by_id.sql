SELECT id, name, avatar, bio, instagram_url, twitter_url, tiktok_url
FROM users
WHERE id = :id
LIMIT 1
