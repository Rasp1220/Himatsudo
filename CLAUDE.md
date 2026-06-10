# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Himatsudo** is a full-stack article CMS platform:
- **API** (`src/`) — PHP 8.1+ BearSunday + Aura SQL
- **Frontend** (`frontend/`) — PHP 8.1+ BearSunday + Qiq templates (user site)
- **CMS** (`cms/`) — Vue 3 + TypeScript (admin panel)
- **Database** — MySQL 8.0+ (users, categories, articles, refresh_tokens)

---

## Terminology Guide for Prompts

When discussing this codebase:
- **"Qiq template side" / "Frontend"** = User-facing site (`frontend/`) — server-side rendered public pages
- **"Vue side" / "CMS"** = Admin panel (`cms/`) — single-page app for content management

---

## Development Commands

```bash
composer install                       # PHP dependencies
npm run build:css                      # Compile SCSS
npm run watch:css                      # Watch SCSS
php bin/setup.php                      # Initialize DB, migrations, seeds
php bin/migrate.php [--fresh]          # Run/reset migrations

# Run services
php -S localhost:8080 -t public        # API
cd frontend && php -S localhost:8081 -t public  # Frontend
cd cms && npm run dev                  # CMS (port 5173)

# Or all three:
php bin/serve.php
```

---

## Architecture

### PHP Backend (BearSunday)
- **Resource-based routing** (`src/Resource/Page/`) — HTTP verbs map to methods: `onGet()`, `onPost()`, etc.
- **Dependency injection** (`src/Module/`) — `AppModule` binds services, database, JWT config
- **Domain objects** (`src/Domain/`) — Immutable readonly classes with `fromArray()` / `toArray()`
- **Services** (`src/Service/`) — Implement interfaces, injected via constructor
- **Authentication** — `RequireAuth` attribute + `AuthInterceptor` validates JWT, refresh tokens stored in DB

### Vue 3 CMS (`cms/`)
- **Pinia stores** (`src/stores/`) — Composition API with async actions calling API
- **Axios client** (`src/api/client.ts`) — Auto-injects Bearer token, handles 401 refresh
- **Typed interfaces** (`src/types/index.ts`) — Match API response shapes
- **Components** — Functional SFCs with `<script setup lang="ts">`

---

## Coding Conventions

### PHP
- `declare(strict_types=1);` in every file
- Type all parameters, returns, properties; use `readonly`
- Classes: `PascalCase`, methods/properties: `camelCase`
- DB columns: `snake_case` (property `eyeCatchImage` → column `eye_catch_image`)
- Resource methods return `$this`; set `$this->code` and `$this->body`
- Validation errors: `$this->code = 422; $this->body = ['error' => '...']`

### Qiq Templates
**✓ Correct:** Use `{{ variable }}` syntax  
**✗ Wrong:** `<?php echo $variable; ?>`

### Vue 3 + TypeScript
- SFCs use `<script setup lang="ts">`, `<template>`, `<style scoped>`
- Stores: `defineStore('name', () => { ... })` with Composition API
- Props: typed with interface or `withDefaults()`
- API calls from `@/api/client.ts` only, never axios directly
- Form data serialized to snake_case before POST/PUT

---

## Database Schema

- `users` — id, email, password_hash, name, role, created_at, updated_at
- `categories` — id, name, slug, type, description, created_at, updated_at
- `articles` — title, slug, status, content, blocks (JSON), excerpt, eye_catch_image, category_id (FK), author_id (FK), youtube_*, published_at, created_at, updated_at
- `refresh_tokens` — id, user_id (FK), token, expires_at, created_at

---

## Key Integrations

- **JWT** — `lcobucci/jwt`, refresh tokens in DB
- **SQL** — Aura SQL (type-safe, no ORM)
- **State** — Pinia (auth, articles, categories, users)
- **Upload** — Base64 JSON to API (not multipart)
- **CSS** — SCSS compiled, Tailwind in CMS
- **Templates** — Qiq (frontend), Vue (CMS)
