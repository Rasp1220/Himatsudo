<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { articlesApi, categoriesApi, usersApi } from '@/api/client'

const statCards = ref([
  { icon: '📝', label: '記事数', value: '—' },
  { icon: '🗂️', label: 'カテゴリ数', value: '—' },
  { icon: '👤', label: 'ユーザー数', value: '—' },
])

onMounted(async () => {
  try {
    const [articles, categories, users] = await Promise.all([
      articlesApi.list({ per_page: 1 }),
      categoriesApi.list(),
      usersApi.list(1, 1),
    ])
    statCards.value[0].value = String(articles.total)
    statCards.value[1].value = String(categories.length)
    statCards.value[2].value = String(users.total)
  } catch {
    // silently ignore dashboard load failures
  }
})
</script>

<template>
  <div>
    <h2 class="text-xl font-bold text-gray-800 mb-6">ダッシュボード</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
      <div
        v-for="card in statCards"
        :key="card.label"
        class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 flex items-center gap-4"
      >
        <div class="text-3xl">{{ card.icon }}</div>
        <div>
          <p class="text-sm text-gray-500">{{ card.label }}</p>
          <p class="text-2xl font-bold text-gray-800">{{ card.value }}</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
      <h3 class="font-semibold text-gray-700 mb-4">クイックアクション</h3>
      <div class="flex flex-wrap gap-3">
        <RouterLink
          to="/articles/new"
          class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700"
        >
          ＋ 通常記事を作成
        </RouterLink>
        <RouterLink
          to="/articles/youtube/new"
          class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700"
        >
          ▶ YouTube記事を作成
        </RouterLink>
        <RouterLink
          to="/categories"
          class="px-4 py-2 bg-slate-600 text-white text-sm font-medium rounded-md hover:bg-slate-700"
        >
          🗂️ カテゴリ管理
        </RouterLink>
      </div>
    </div>
  </div>
</template>
