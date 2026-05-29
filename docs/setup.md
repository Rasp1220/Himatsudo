# Environment Setup & Deployment Guide

## Requirements

| Tool | Version |
|------|---------|
| PHP | 8.1+ |
| Composer | 2.x |
| MySQL | 8.0+ |
| Node.js | 18+ |
| npm | 9+ |
| Web server | Nginx / Apache |

---

## 1. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE himatsudo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations (in order)
mysql -u root -p himatsudo < database/migrations/001_create_users_table.sql
mysql -u root -p himatsudo < database/migrations/002_create_categories_table.sql
mysql -u root -p himatsudo < database/migrations/003_create_articles_table.sql
mysql -u root -p himatsudo < database/migrations/004_create_refresh_tokens_table.sql

# Seed initial data
mysql -u root -p himatsudo < database/seeds/001_seed_admin_user.sql
mysql -u root -p himatsudo < database/seeds/002_seed_default_categories.sql
```

---

## 2. API Setup

```bash
cd api
composer install --no-dev --optimize-autoloader
```

Create `/api/.env`:
```
DB_DSN=mysql:host=localhost;dbname=himatsudo;charset=utf8mb4
DB_USER=himatsudo_user
DB_PASSWORD=your_secure_password
JWT_SECRET=your-very-long-and-random-jwt-secret-key-here-minimum-32-characters
```

### Nginx configuration
```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    root /var/www/himatsudo/api/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 3. Frontend Setup

```bash
cd frontend
composer install --no-dev --optimize-autoloader
```

Create `/frontend/.env` (same DB credentials as API).

### Nginx configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/himatsudo/frontend/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 4. CMS Setup

```bash
cd cms
npm install
```

Create `/cms/.env`:
```
VITE_API_BASE_URL=https://api.yourdomain.com
```

### Build for production
```bash
npm run build
# Output: cms/dist/
```

### Nginx configuration (serve CMS from subdomain)
```nginx
server {
    listen 80;
    server_name cms.yourdomain.com;
    root /var/www/himatsudo/cms/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

---

## 5. Environment Variables Reference

### PHP Apps (API / Frontend)
Read from `$_ENV` or `.env` via a loader. Set as FastCGI params in Nginx:
```nginx
fastcgi_param DB_DSN "mysql:host=localhost;dbname=himatsudo;charset=utf8mb4";
fastcgi_param DB_USER "himatsudo_user";
fastcgi_param DB_PASSWORD "your_password";
fastcgi_param JWT_SECRET "your_jwt_secret";
```

Or use `vlucas/phpdotenv` by adding it to `composer.json` and loading in `public/index.php`.

---

## 6. CORS (API)

For cross-origin CMS requests, add CORS headers in Nginx:
```nginx
add_header 'Access-Control-Allow-Origin' 'https://cms.yourdomain.com' always;
add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
add_header 'Access-Control-Allow-Headers' 'Authorization, Content-Type' always;
```

---

## 7. Permissions

```bash
chmod -R 775 api/var/
chmod -R 775 frontend/var/
chown -R www-data:www-data api/ frontend/ cms/dist/
```

---

## 8. Production Checklist

- [ ] Change `JWT_SECRET` to a unique 32+ character random string
- [ ] Set DB credentials for a dedicated database user (not root)
- [ ] Enable HTTPS (Let's Encrypt / SSL certificate)
- [ ] Set `APP_ENV=prod` context in bootstrap files
- [ ] Run `composer dump-autoload --optimize` after deployment
- [ ] Run `npm run build` and serve `cms/dist/` statically
- [ ] Configure log rotation for `api/var/log/` and `frontend/var/log/`
