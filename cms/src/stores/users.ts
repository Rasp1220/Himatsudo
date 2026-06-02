import { defineStore } from 'pinia'
import { ref } from 'vue'
import { usersApi } from '@/api/client'
import type { PaginatedResponse, User } from '@/types'

export const useUsersStore = defineStore('users', () => {
  const items = ref<User[]>([])
  const pagination = ref({ total: 0, page: 1, per_page: 20, last_page: 1 })
  const loading = ref(false)

  async function fetchList(page = 1, perPage = 20) {
    loading.value = true
    try {
      const data: PaginatedResponse<User> = await usersApi.list(page, perPage)
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

  async function create(data: Partial<User> & { password: string }) {
    const u = await usersApi.create(data)
    items.value.unshift(u)
    return u
  }

  async function update(id: number, data: Partial<User> & { password?: string }) {
    const u = await usersApi.update(id, data)
    const idx = items.value.findIndex((i) => i.id === id)
    if (idx !== -1) items.value[idx] = u
    return u
  }

  async function remove(id: number) {
    await usersApi.delete(id)
    items.value = items.value.filter((u) => u.id !== id)
  }

  return { items, pagination, loading, fetchList, create, update, remove }
})
