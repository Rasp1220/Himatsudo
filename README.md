# Himatsudo - Article CMS Platform

A full-stack article CMS platform consisting of:

| Component | Stack | Directory |
|-----------|-------|-----------|
| REST API | PHP 8.1+ · BearSunday · MySQL | `api/` |
| Frontend (public site) | PHP 8.1+ · BearSunday · Qiq templates | `frontend/` |
| CMS (admin panel) | Vue 3 · TypeScript · Tailwind CSS | `cms/` |
| Database | MySQL 8.0+ | `var/db/` |

---

## Quick Start

### 1. Database
```bash
mysql -u root -p < var/db/migrations/001_create_users_table.sql
mysql -u root -p < var/db/migrations/002_create_categories_table.sql
mysql -u root -p < var/db/migrations/003_create_articles_table.sql
mysql -u root -p < var/db/migrations/004_create_refresh_tokens_table.sql
mysql -u root -p < var/db/seeds/001_seed_admin_user.sql
mysql -u root -p < var/db/seeds/002_seed_default_categories.sql
```
Default admin: `admin@example.com` / `Admin1234!`

### 2. API
```bash
cd api
cp .env.example .env    # edit DB_DSN, DB_USER, DB_PASSWORD, JWT_SECRET
composer install
php -S localhost:8080 -t public
```

### 3. Frontend
```bash
cd frontend
cp .env.example .env    # edit DB_DSN etc.
composer install
php -S localhost:8081 -t public
```

### 4. CMS
```bash
cd cms
cp .env.example .env    # set VITE_API_BASE_URL=http://localhost:8080
npm install
npm run dev             # http://localhost:5173
```

---

## Environment Variables

### API / Frontend (`.env`)
| Variable | Default | Description |
|----------|---------|-------------|
| `DB_DSN` | `mysql:host=localhost;dbname=himatsudo;charset=utf8mb4` | PDO DSN |
| `DB_USER` | `root` | DB username |
| `DB_PASSWORD` | *(empty)* | DB password |
| `JWT_SECRET` | *(default, insecure)* | JWT signing secret — **change in production** |

### CMS (`.env`)
| Variable | Default | Description |
|----------|---------|-------------|
| `VITE_API_BASE_URL` | `/api` | Base URL of the API |

---

## Documentation
- [API Specification](docs/api-spec.md)
- [Setup & Deployment Guide](docs/setup.md)
- [CMS Operation Manual (JP)](docs/cms-manual.md)
