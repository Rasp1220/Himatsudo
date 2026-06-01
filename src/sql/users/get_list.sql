SELECT id, name, email, role, created_at, updated_at
FROM users
ORDER BY id DESC
LIMIT :limit OFFSET :offset
