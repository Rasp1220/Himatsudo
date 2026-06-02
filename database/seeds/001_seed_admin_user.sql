-- seed: 001 default admin user
-- password: Admin1234! (bcrypt hashed)
INSERT INTO users (name, email, password, role) VALUES (
    '管理者',
    'admin@example.com',
    '$2y$12$vMw9vu7nI8XAc.xRUs6/YO7nX8pBVA.gvOKCbAtA1KH.EmwemnfZW',
    'admin'
) ON DUPLICATE KEY UPDATE name = VALUES(name);
