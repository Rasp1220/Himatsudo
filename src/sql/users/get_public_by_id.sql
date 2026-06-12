SELECT id, name, avatar, bio
FROM users
WHERE id = :id
LIMIT 1
