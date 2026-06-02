# API Specification

Base URL: `http://your-domain/` (production) or `http://localhost:8080/` (development)

All requests and responses use `Content-Type: application/json`.  
Protected endpoints require `Authorization: Bearer <access_token>`.

---

## Authentication

### POST /auth/login
Login and obtain tokens.

**Request body:**
```json
{ "email": "admin@example.com", "password": "Admin1234!" }
```

**Response 200:**
```json
{
  "access_token": "eyJ...",
  "refresh_token": "eyJ...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": { "id": 1, "name": "管理者", "email": "admin@example.com", "role": "admin" }
}
```

### POST /auth/logout
🔒 Invalidate the current session.

**Request body:** `{ "refresh_token": "eyJ..." }` *(optional)*  
**Response:** 204 No Content

### POST /auth/refresh
Obtain a new access token using a refresh token.

**Request body:** `{ "refresh_token": "eyJ..." }`  
**Response 200:** Same structure as login response.

---

## Users

### GET /users 🔒
List users with pagination.

**Query params:** `page` (default 1), `per_page` (default 20)

**Response 200:**
```json
{
  "items": [{ "id": 1, "name": "管理者", "email": "...", "role": "admin", ... }],
  "total": 1, "page": 1, "per_page": 20, "last_page": 1
}
```

### POST /users 🔒
Create a user.

**Request body:** `{ "name": "...", "email": "...", "password": "...", "role": "editor" }`  
**Response:** 201 with user object

### GET /user?id={id} 🔒
Get a single user.

### PUT /user?id={id} 🔒
Update a user. All fields optional (password omitted = unchanged).

### DELETE /user?id={id} 🔒
Delete a user. **Response:** 204

---

## Categories

### GET /categories
List all categories (public).

**Response 200:** Array of category objects
```json
[{ "id": 1, "name": "通常記事", "slug": "normal", "type": "normal", "sort_order": 1, ... }]
```

### POST /categories 🔒
Create a category.

**Request body:** `{ "name": "...", "slug": "...", "type": "custom", "sort_order": 0 }`  
**`type` values:** `normal` | `blog` | `youtube` | `custom`

### GET /category?id={id}
Get a single category (public).

### PUT /category?id={id} 🔒
Update a category. All fields optional.

### DELETE /category?id={id} 🔒
Delete a category. **Response:** 204

---

## Articles

### GET /articles
List published articles (public).

**Query params:** `page`, `per_page` (default 15), `category_id`

**Response 200:**
```json
{
  "items": [{ "id": 1, "title": "...", "slug": "...", "excerpt": "...", ... }],
  "total": 100, "page": 1, "per_page": 15, "last_page": 7
}
```

### POST /articles 🔒
Create an article.

**Request body:**
```json
{
  "title": "記事タイトル",
  "slug": "article-slug",
  "author_id": 1,
  "status": "draft",
  "content": "<p>本文HTML</p>",
  "excerpt": "概要",
  "eye_catch_image": "https://...",
  "category_id": 1,
  "youtube_url": null,
  "youtube_video_id": null,
  "youtube_thumbnail": null
}
```

### GET /article?id={id}
Get a single article (public — returns 404 if not found, ignores status).

### PUT /article?id={id} 🔒
Update an article. All fields optional.

### DELETE /article?id={id} 🔒
Delete an article. **Response:** 204

---

## YouTube Import

### POST /articles/youtube-import 🔒
Fetch YouTube video metadata from a URL or video ID.

**Request body:** `{ "url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ" }`

**Response 200:**
```json
{
  "video_id": "dQw4w9WgXcQ",
  "title": "Rick Astley - Never Gonna Give You Up",
  "thumbnail": "https://img.youtube.com/vi/dQw4w9WgXcQ/maxresdefault.jpg",
  "youtube_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
  "embed_url": "https://www.youtube.com/embed/dQw4w9WgXcQ"
}
```

---

## Error Responses

| Code | Meaning |
|------|---------|
| 400 | Bad Request – missing/invalid parameters |
| 401 | Unauthorized – missing or invalid Bearer token |
| 404 | Not Found |
| 422 | Unprocessable Entity – validation error |
| 500 | Internal Server Error |

Error body: `{ "error": "...", "message": "..." }`
