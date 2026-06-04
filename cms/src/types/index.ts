export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'editor'
  created_at: string
  updated_at: string
}

export interface Category {
  id: number
  name: string
  slug: string
  type: 'normal' | 'blog' | 'youtube' | 'custom'
  sort_order: number
  created_at: string
  updated_at: string
}

export type ArticleStatus = 'draft' | 'published'

export interface Article {
  id: number
  title: string
  slug: string
  content: string | null
  blocks: string | null
  excerpt: string | null
  eye_catch_image: string | null
  category_id: number | null
  category_name: string | null
  category_type: string | null
  author_id: number
  author_name: string | null
  status: ArticleStatus
  youtube_url: string | null
  youtube_video_id: string | null
  youtube_thumbnail: string | null
  related_article_ids: number[] | null
  published_at: string | null
  created_at: string
  updated_at: string
}

export interface PaginatedResponse<T> {
  items: T[]
  total: number
  page: number
  per_page: number
  last_page: number
}

export interface AuthState {
  user: User | null
  accessToken: string | null
  refreshToken: string | null
}

export interface LoginPayload {
  email: string
  password: string
}

export interface LoginResponse {
  access_token: string
  refresh_token: string
  token_type: string
  expires_in: number
  user: User
}

export interface RefreshResponse {
  access_token: string
  refresh_token: string
  token_type: string
  expires_in: number
}

// Block-based article content
export interface HeadingBlock {
  id: string
  type: 'heading'
  level: 2 | 3 | 4
  text: string
}

export interface TextBlock {
  id: string
  type: 'text'
  html: string
}

export interface ImageBlock {
  id: string
  type: 'image'
  url: string
  alt: string
  caption: string
}

export interface VideoBlock {
  id: string
  type: 'video'
  youtube_url: string
  video_id: string
  caption: string
}

export type ArticleBlock = HeadingBlock | TextBlock | ImageBlock | VideoBlock

export interface ArticleFormData {
  title: string
  slug: string
  content: string
  blocks?: string
  excerpt: string
  eye_catch_image: string
  category_id: number | null
  status: ArticleStatus
  published_at?: string | null
  youtube_url: string
  youtube_video_id: string
  youtube_thumbnail: string
}

export interface YoutubeImportResult {
  video_id: string
  title: string
  thumbnail: string
  youtube_url: string
  embed_url: string
  description: string
  published_at: string
}

export interface ApiError {
  error: string
  message?: string
}
