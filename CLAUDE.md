# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Himatsudo** is a full-stack article CMS platform with three main components:

1. **REST API** (`src/` + `public/api/`) — PHP 8.1+ using BearSunday framework with Aura SQL. Handles authentication, CRUD operations for articles/categories/users, YouTube imports, and file uploads.
2. **Frontend** (`frontend/` + `public/`) — PHP 8.1+ using BearSunday + Qiq templates. Renders public-facing article listing and single article pages.
3. **CMS Admin Panel** (`cms/`) — Vue 3 + TypeScript with Tailwind CSS. Single-page app for content management, user/category/article administration.
4. **Database** (`var/db/migrations/`) — MySQL 8.0+ with seed data. Tables: users, categories, articles, refresh_tokens.

The API is shared between frontend (server-side rendering) and CMS (SPA). Authentication uses JWT with refresh tokens stored in the database.

---

## Development Commands

### Root-Level Commands

| Command | Purpose |
|---------|---------|
| `composer install` | Install PHP dependencies |
| `npm run build:css` | Compile SCSS to CSS (resources/scss → public/css) |
| `npm run watch:css` | Watch SCSS and rebuild on changes |
| `php bin/setup.php` | Initialize environment (creates DB, runs migrations, seeds) |
| `php bin/migrate.php` | Run pending migrations |
| `php bin/migrate.php --fresh` | Drop and re-run all migrations |

### Running the Application

**Option 1: Individual Services** (3 terminal tabs)
```bash
# Terminal 1: API (localhost:8080)
php -S localhost:8080 -t public

# Terminal 2: Frontend (localhost:8081)
cd frontend && php -S localhost:8081 -t public

# Terminal 3: CMS (localhost:5173)
cd cms && npm install && npm run dev
```

**Option 2: Convenience Scripts**
```bash
php bin/serve.php              # Launches all three via background processes
npm run serve                  # Alias for bin/serve.php
npm run serve:app              # API only
npm run serve:cms              # CMS only
```

### CMS-Specific Commands

```bash
cd cms
npm install                    # Install dependencies
npm run dev                    # Dev server with HMR (port 5173)
npm run build                  # Type-check (vue-tsc) then build
npm run typecheck              # Run TypeScript type checking without building
npm run preview                # Preview built output locally
```

### Database & Utilities

```bash
php bin/migrate.php            # Run pending migrations
php bin/qiq-clear.php          # Clear Qiq template cache
php bin/youtube-import.php     # CLI utility for YouTube article imports
```

---

## Architecture

### PHP Backend (BearSunday)

**Dependency Injection Container** (`src/Module/`)
- `App.php` — Root application class extending `AbstractApp`
- `AppModule.php` — Binds domain services, database connection, JWT config
- `ApiModule.php` — API-specific module with auth interceptor
- `HtmlModule.php` — Frontend HTML renderer (Qiq)

**Resource-Based Routing** (`src/Resource/Page/`)
- BearSunday uses method names to route HTTP verbs: `onGet()`, `onPost()`, `onPut()`, `onDelete()`
- URI path maps to class path. Example: `/admin/api/articles` → `Resource/Page/Admin/Api/Articles`
- Query params become method arguments with PHPdoc type hints
- All methods return `$this` (the ResourceObject) with `$this->code` and `$this->body` set

**Domain Objects** (`src/Domain/`)
- Immutable readonly classes (PHP 8.1+) representing core entities: `Article`, `User`, `Category`
- Implement factory method `fromArray()` to hydrate from database rows
- Implement `toArray()` to serialize to JSON responses
- Property names use camelCase; DB columns use snake_case (handled in factory)

**Interfaces** (`src/Interfaces/`)
- Service contracts for dependency injection. Example: `ArticleInterface` specifies `getAdminList()`, `create()`, `update()`, `delete()`
- Bound in DI container via `AppModule`

**Services** (`src/Service/`)
- Implement interfaces. Example: `ArticleService` loads articles from database, handles filtering/pagination, calls domain factories
- Injected via constructor into Resource classes using readonly properties
- Sql queries defined in separate `.sql` files (in `sql/` directory) referenced via `SqlFileTrait`

**Authentication** (`src/Auth/`, `src/Annotation/`, `src/Interceptor/`)
- `RequireAuth` attribute marks methods requiring JWT validation
- `AuthInterceptor` validates Bearer tokens, injects current user into request context
- `JwtService` signs/validates tokens using `lcobucci/jwt` library
- Refresh tokens stored in DB for token rotation

**Error Handling**
- Set `$this->code` to HTTP status (401, 422, 201, etc.)
- Return `$this->body = ['error' => '...']` for client errors
- Let exceptions propagate; framework returns 500

### Vue 3 CMS Frontend (`cms/`)

**Stores** (`cms/src/stores/`)
- Pinia store per resource: `articles`, `categories`, `users`, `auth`
- Composition API with reactive refs for data and loading state
- Async action functions that call API, update state, return result
- Import and inject in components: `const store = useArticlesStore()`

**API Client** (`cms/src/api/client.ts`)
- Axios instance with request/response interceptors
- `Authorization: Bearer <token>` added automatically if token in localStorage
- 401 responses trigger token refresh with refresh token (stored in DB)
- File upload uses base64 JSON instead of multipart (required by BearSunday JSON context)
- Typed API modules: `authApi`, `usersApi`, `categoriesApi`, `articlesApi`, `uploadApi`

**Components** (`cms/src/components/`)
- Functional components with `<script setup lang="ts">` syntax
- Emit events to parent, use slots for composition
- Blocks for article content: `TextBlock`, `ImageBlock`, `HeadingBlock`, `VideoBlock`
- Shared UI: `DataTable`, `Pagination`, `ConfirmModal`, `TinyMceEditor`, `BlockEditor`

**Views** (`cms/src/views/`)
- Page components: login, dashboard, articles list/edit, users, categories
- Use stores and router for navigation
- Form submission via `handleSubmit()` that calls store action, redirects on success

**Router** (`cms/src/router/index.ts`)
- Vue Router 4 with role-based guards (auth, non-auth)
- Routes: `/login`, `/dashboard`, `/articles`, `/articles/:id`, `/users`, `/categories`

**Types** (`cms/src/types/index.ts`)
- TypeScript interfaces for all API responses: `Article`, `User`, `Category`, `LoginResponse`, `PaginatedResponse<T>`
- Reflect API contract; keep in sync with PHP responses

---

## Coding Conventions

### PHP Conventions

1. **Strict Types & Type Hints**
   - `declare(strict_types=1);` at top of every file
   - Type all parameters, return types, properties. Use `readonly` for immutable class properties.
   - `?Type` for nullable, `?int | null` not needed

2. **Naming**
   - Namespaces and classes: `PascalCase`
   - Methods, properties, variables: `camelCase`
   - Constants: `SCREAMING_SNAKE_CASE`
   - DB columns: `snake_case` (Article property `eyeCatchImage` → DB column `eye_catch_image`)

3. **Classes**
   - Domain objects: `final readonly class` with constructor property promotion
   - Services: Implement interface, use constructor DI with `readonly`
   - Resources: Extend `ResourceObject`, use constructor DI, return `$this`

4. **Methods**
   - Resource methods: `onGet()`, `onPost()`, `onPut()`, `onDelete()`, etc.
   - Always return `$this` from resource methods
   - Set `$this->code` (HTTP status) and `$this->body` (JSON response)
   - Service methods should be public, business logic focused

5. **Error Handling**
   - Validation errors → `$this->code = 422; $this->body = ['error' => '...']`
   - Not found → `$this->code = 404; $this->body = ['error' => 'Not found']`
   - Auth required → interceptor handles (throw or deny)

### Vue 3 + TypeScript Conventions

1. **File Structure**
   - SFCs use `.vue` extension with `<script setup lang="ts">`, `<template>`, `<style scoped>`
   - TypeScript in `.ts` files (stores, API client, types, utilities)

2. **Component Naming**
   - File names: `PascalCase.vue` → auto-imported as `<PascalCase />` (configure in vite.config)
   - Components: functional with `<script setup>` (no `name` property needed)

3. **Typing**
   - Define types in `types/index.ts`, reuse across app
   - Typed props with `withDefaults()` or explicit interface
   - Use `as const` for string literals with type safety

4. **Stores (Pinia)**
   - `defineStore('storeName', () => { ... })`  — composition API style
   - Refs for state, normal functions for actions
   - Import with `const store = useArticlesStore()`
   - Actions are async (return Promise)

5. **API Client**
   - Call methods from `@/api/client.ts`, never axios directly
   - All endpoints typed with interfaces from `@/types`
   - Error handling in components (via try/catch in actions)

6. **Forms & Validation**
   - Form data managed in component refs or store
   - Serialize to API shape (snake_case) before POST/PUT
   - On 422 error, display `error` message to user
   - Redirect on success

---

## Database Schema

Migrations in `var/db/migrations/` create:
- `users` — id, email, password_hash, name, role, created_at, updated_at
- `categories` — id, name, slug, type, description, created_at, updated_at
- `articles` — id, title, slug, status (draft/published), content, blocks (JSON), excerpt, eye_catch_image, category_id (FK), author_id (FK), youtube_url, youtube_video_id, youtube_thumbnail, published_at, created_at, updated_at
- `refresh_tokens` — id, user_id (FK), token, expires_at, created_at

Seeds in `var/db/seeds/` populate default admin user and categories.

---

## Key Integrations

- **JWT Authentication** — `lcobucci/jwt` for signing; refresh tokens in DB for rotation
- **Database** — Aura SQL for type-safe queries, no ORM
- **Vue State** — Pinia for global state (auth, articles, categories, users)
- **File Upload** — Base64 JSON to API (not multipart)
- **YouTube Integration** — `youtube-import.php` CLI tool, stores metadata in articles table
- **CSS** — SCSS compiled to public/css/main.build.css via npm, Tailwind in CMS
- **Template Engine** — Qiq (frontend) for server-side rendering, Vue (CMS) for SPA
