import { defineStore } from 'pinia'
import { ref } from 'vue'
import { articlesApi } from '@/api/client'
import type { Article, ArticleFormData, PaginatedResponse } from '@/types'

export const useArticlesStore = defineStore('articles', () => {
  const items = ref<Article[]>([])
  const current = ref<Article | null>(null)
  const pagination = ref({ total: 0, page: 1, per_page: 20, last_page: 1 })
  const loading = ref(false)

  async function fetchList(params?: {
    page?: number
    per_page?: number
    category_id?: number | null
    status?: string | null
    keyword?: string | null
  }) {
    loading.value = true
    try {
      const data: PaginatedResponse<Article> = await articlesApi.list(params)
      items.value = data.items
      pagination.value = {
        total: data.total,
        page: data.page,
        per_page: data.per_page,
        last_page: data.last_page,
      }
    } finally {
      loading.value = false
    }
  }

  async function fetchOne(id: number) {
    loading.value = true
    try {
      current.value = await articlesApi.get(id)
    } finally {
      loading.value = false
    }
  }

  async function create(data: ArticleFormData & { author_id: number }) {
    const article = await articlesApi.create(data)
    return article
  }

  async function update(id: number, data: Partial<ArticleFormData>) {
    const article = await articlesApi.update(id, data)
    current.value = article
    return article
  }

  async function remove(id: number) {
    await articlesApi.delete(id)
    items.value = items.value.filter((a) => a.id !== id)
  }

  return { items, current, pagination, loading, fetchList, fetchOne, create, update, remove }
})
