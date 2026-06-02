import { defineStore } from 'pinia'
import { ref } from 'vue'
import { categoriesApi } from '@/api/client'
import type { Category } from '@/types'

export const useCategoriesStore = defineStore('categories', () => {
  const items = ref<Category[]>([])
  const loading = ref(false)

  async function fetchAll() {
    loading.value = true
    try {
      items.value = await categoriesApi.list()
    } finally {
      loading.value = false
    }
  }

  async function create(data: Omit<Category, 'id' | 'created_at' | 'updated_at'>) {
    const category = await categoriesApi.create(data)
    items.value.push(category)
    return category
  }

  async function update(id: number, data: Partial<Omit<Category, 'id' | 'created_at' | 'updated_at'>>) {
    const category = await categoriesApi.update(id, data)
    const idx = items.value.findIndex((c) => c.id === id)
    if (idx !== -1) items.value[idx] = category
    return category
  }

  async function remove(id: number) {
    await categoriesApi.delete(id)
    items.value = items.value.filter((c) => c.id !== id)
  }

  return { items, loading, fetchAll, create, update, remove }
})
