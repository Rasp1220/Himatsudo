SELECT id, name, email, role, avatar, bio, created_at, updated_at
FROM users
WHERE id = :id
LIMIT 1
