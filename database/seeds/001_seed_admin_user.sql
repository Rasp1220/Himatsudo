-- seed: 001 default admin user
-- password: Admin1234! (bcrypt hashed)
INSERT INTO users (name, email, password, role) VALUES (
    '管理者',
    'admin@example.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
) ON DUPLICATE KEY UPDATE name = VALUES(name);
