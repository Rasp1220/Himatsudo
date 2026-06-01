SELECT id, name, email, password, role, created_at, updated_at
FROM users
WHERE email = :email
LIMIT 1
