/**
 * mirrors cms/src/stores/articles.ts
 */
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useArticlesStore } from '@/stores/articles'

vi.mock('@/api/client', () => ({
  articlesApi: {
    list: vi.fn(),
    get: vi.fn(),
    create: vi.fn(),
    update: vi.fn(),
    delete: vi.fn(),
  },
}))

import { articlesApi } from '@/api/client'

const makeArticle = (id: number = 1) => ({
  id,
  title: `Article ${id}`,
  slug: `article-${id}`,
  content: '<p>content</p>',
  blocks: null,
  excerpt: 'excerpt',
  eye_catch_image: null,
  category_id: null,
  category_name: null,
  category_type: null,
  author_id: 1,
  author_name: 'Admin',
  status: 'published' as const,
  youtube_url: null,
  youtube_video_id: null,
  youtube_thumbnail: null,
  published_at: '2024-01-01T00:00:00Z',
  created_at: '2024-01-01T00:00:00Z',
  updated_at: '2024-01-01T00:00:00Z',
})

const makePaginated = (items: ReturnType<typeof makeArticle>[]) => ({
  items,
  total: items.length,
  page: 1,
  per_page: 20,
  last_page: 1,
})

describe('useArticlesStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('initialises with empty state', () => {
    const store = useArticlesStore()
    expect(store.items).toEqual([])
    expect(store.current).toBeNull()
    expect(store.loading).toBe(false)
  })

  it('fetchList populates items and pagination', async () => {
    const articles = [makeArticle(1), makeArticle(2)]
    vi.mocked(articlesApi.list).mockResolvedValue(makePaginated(articles))

    const store = useArticlesStore()
    await store.fetchList()

    expect(store.items).toHaveLength(2)
    expect(store.pagination.total).toBe(2)
    expect(store.loading).toBe(false)
  })

  it('fetchList sets loading to false even on error', async () => {
    vi.mocked(articlesApi.list).mockRejectedValue(new Error('network'))

    const store = useArticlesStore()
    await expect(store.fetchList()).rejects.toThrow('network')
    expect(store.loading).toBe(false)
  })

  it('fetchOne sets current article', async () => {
    const article = makeArticle(5)
    vi.mocked(articlesApi.get).mockResolvedValue(article)

    const store = useArticlesStore()
    await store.fetchOne(5)

    expect(store.current).toEqual(article)
    expect(store.loading).toBe(false)
  })

  it('create returns the created article', async () => {
    const article = makeArticle(10)
    vi.mocked(articlesApi.create).mockResolvedValue(article)

    const store = useArticlesStore()
    const result = await store.create({
      title: 'New',
      slug: 'new',
      content: '',
      excerpt: '',
      eye_catch_image: '',
      category_id: null,
      status: 'draft',
      youtube_url: '',
      youtube_video_id: '',
      youtube_thumbnail: '',
      author_id: 1,
    })

    expect(result.id).toBe(10)
  })

  it('update sets current and returns updated article', async () => {
    const updated = makeArticle(3)
    updated.title = 'Updated Title'
    vi.mocked(articlesApi.update).mockResolvedValue(updated)

    const store = useArticlesStore()
    const result = await store.update(3, { title: 'Updated Title' })

    expect(result.title).toBe('Updated Title')
    expect(store.current?.title).toBe('Updated Title')
  })

  it('remove deletes item from list', async () => {
    vi.mocked(articlesApi.list).mockResolvedValue(makePaginated([makeArticle(1), makeArticle(2)]))
    vi.mocked(articlesApi.delete).mockResolvedValue(undefined as never)

    const store = useArticlesStore()
    await store.fetchList()
    await store.remove(1)

    expect(store.items).toHaveLength(1)
    expect(store.items[0].id).toBe(2)
  })

  it('fetchList passes filter params to API', async () => {
    vi.mocked(articlesApi.list).mockResolvedValue(makePaginated([]))

    const store = useArticlesStore()
    await store.fetchList({ status: 'draft', keyword: 'hello' })

    expect(vi.mocked(articlesApi.list)).toHaveBeenCalledWith({ status: 'draft', keyword: 'hello' })
  })
})
