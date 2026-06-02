SELECT id, name, email, role, created_at, updated_at
FROM users
WHERE id = :id
LIMIT 1
