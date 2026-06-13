import axios, { type AxiosInstance, type AxiosError } from 'axios'
import type {
  Article,
  ArticleFormData,
  Category,
  LoginPayload,
  LoginResponse,
  RefreshResponse,
  PaginatedResponse,
  ProfileFormData,
  User,
  YoutubeImportResult,
} from '@/types'

const BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8080/admin/api'

function createClient(): AxiosInstance {
  const client = axios.create({ baseURL: BASE_URL })

  client.interceptors.request.use((config) => {
    const token = localStorage.getItem('access_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  })

  client.interceptors.response.use(
    (res) => res,
    async (error: AxiosError) => {
      if (error.response?.status === 401) {
        const refreshToken = localStorage.getItem('refresh_token')
        if (refreshToken && error.config && !error.config.url?.includes('/auth/refresh')) {
          try {
            const { data } = await axios.post<RefreshResponse>(`${BASE_URL}/auth/refresh`, {
              refresh_token: refreshToken,
            })
            localStorage.setItem('access_token', data.access_token)
            localStorage.setItem('refresh_token', data.refresh_token)
            error.config.headers.Authorization = `Bearer ${data.access_token}`
            return client.request(error.config)
          } catch {
            localStorage.removeItem('access_token')
            localStorage.removeItem('refresh_token')
            window.location.href = '/login'
          }
        } else {
          localStorage.removeItem('access_token')
          localStorage.removeItem('refresh_token')
          window.location.href = '/login'
        }
      }
      return Promise.reject(error)
    },
  )

  return client
}

const http = createClient()

// Auth
export const authApi = {
  login: (payload: LoginPayload) =>
    http.post<LoginResponse>('/auth/login', payload).then((r) => r.data),

  logout: (refreshToken?: string) =>
    http.post('/auth/logout', { refresh_token: refreshToken }),

  refresh: (refreshToken: string) =>
    http.post<RefreshResponse>('/auth/refresh', { refresh_token: refreshToken }).then((r) => r.data),

  me: () => http.get<User>('/auth/me').then((r) => r.data),

  // 自分のプロフィール取得・更新（ログイン中の本人のみ。対象はトークンの uid）
  getProfile: () => http.get<User>('/auth/profile').then((r) => r.data),

  updateProfile: (data: ProfileFormData) =>
    http.put<User>('/auth/profile', data).then((r) => r.data),
}

// Users
export const usersApi = {
  list: (page = 1, perPage = 20) =>
    http
      .get<PaginatedResponse<User>>('/users', { params: { page, per_page: perPage } })
      .then((r) => r.data),

  get: (id: number) => http.get<User>(`/user?id=${id}`).then((r) => r.data),

  create: (data: Partial<User> & { password: string }) =>
    http.post<User>('/users', data).then((r) => r.data),

  update: (id: number, data: Partial<User> & { password?: string }) =>
    http.put<User>(`/user?id=${id}`, data).then((r) => r.data),

  delete: (id: number) => http.delete(`/user?id=${id}`),
}

// Categories
export const categoriesApi = {
  list: () => http.get<Category[]>('/categories').then((r) => r.data),

  get: (id: number) => http.get<Category>(`/category?id=${id}`).then((r) => r.data),

  create: (data: Omit<Category, 'id' | 'created_at' | 'updated_at'>) =>
    http.post<Category>('/categories', data).then((r) => r.data),

  update: (id: number, data: Partial<Omit<Category, 'id' | 'created_at' | 'updated_at'>>) =>
    http.put<Category>(`/category?id=${id}`, data).then((r) => r.data),

  delete: (id: number) => http.delete(`/category?id=${id}`),
}

// Articles (admin endpoint for CMS — supports status/keyword filters)
export const articlesApi = {
  list: (params?: {
    page?: number
    per_page?: number
    category_id?: number | null
    status?: string | null
    keyword?: string | null
  }) =>
    http
      .get<PaginatedResponse<Article>>('/articles', { params })
      .then((r) => r.data),

  get: (id: number) => http.get<Article>(`/article?id=${id}`).then((r) => r.data),

  create: (data: ArticleFormData & { author_id: number }) =>
    http.post<Article>('/articles', data).then((r) => r.data),

  update: (id: number, data: Partial<ArticleFormData>) =>
    http.put<Article>(`/article?id=${id}`, data).then((r) => r.data),

  delete: (id: number) => http.delete(`/article?id=${id}`),

  duplicate: async (id: number): Promise<Article> => {
    const orig = await http.get<Article>(`/article?id=${id}`).then((r) => r.data)
    const slug = `${orig.slug}-copy-${Date.now()}`
    return http.post<Article>('/articles', {
      title: `${orig.title} のコピー`,
      slug,
      content: orig.content ?? '',
      blocks: orig.blocks ?? '',
      excerpt: orig.excerpt ?? '',
      eye_catch_image: orig.eye_catch_image ?? '',
      // category_id=0 は「カテゴリなし」を意味する（既存のhandleSubmitと同じ規約）
      category_id: orig.category_id ?? 0,
      status: 'draft',
      author_id: Number(orig.author_id),
      youtube_url: orig.youtube_url ?? '',
      youtube_video_id: orig.youtube_video_id ?? '',
      youtube_thumbnail: orig.youtube_thumbnail ?? '',
    }).then((r) => r.data)
  },

  importYoutube: (url: string) =>
    http
      .post<YoutubeImportResult>('/articles/youtube-import', { url })
      .then((r) => r.data),
}

// File upload — base64 JSON instead of multipart to work with BEAR.Sunday's JSON API context
export const uploadApi = {
  upload: (file: File): Promise<{ url: string }> => {
    return new Promise<{ url: string }>((resolve, reject) => {
      const reader = new FileReader()
      reader.onload = () => {
        const dataUrl = reader.result as string
        const base64 = dataUrl.split(',')[1]
        http
          .post<{ url: string }>('/upload', { data: base64, name: file.name, mime: file.type })
          .then((r) => resolve(r.data))
          .catch(reject)
      }
      reader.onerror = () => reject(new Error('ファイルの読み込みに失敗しました'))
      reader.readAsDataURL(file)
    })
  },
}
