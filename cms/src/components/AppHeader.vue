<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const pageTitles: Record<string, string> = {
  Dashboard: 'ダッシュボード',
  ArticlesList: '記事管理',
  ArticleNew: '記事 新規作成',
  ArticleEdit: '記事 編集',
  YoutubeArticleNew: 'YouTube記事 新規作成',
  YoutubeArticleEdit: 'YouTube記事 編集',
  Categories: 'カテゴリ管理',
  Users: 'ユーザー管理',
  Profile: 'プロフィール設定',
}

const pageTitle = computed(() => pageTitles[String(route.name ?? '')] ?? 'CMS')

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <header class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
    <h1 class="text-lg font-semibold text-gray-700">{{ pageTitle }}</h1>
    <div class="flex items-center gap-4">
      <span v-if="auth.user" class="text-sm text-gray-500">
        <RouterLink to="/profile" class="hover:text-gray-800 hover:underline">{{ auth.user.name }}</RouterLink>
        <span class="ml-1 px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-xs font-medium">
          {{ auth.user.role === 'admin' ? '管理者' : '編集者' }}
        </span>
      </span>
      <button
        @click="handleLogout"
        class="text-sm text-gray-500 hover:text-gray-800 transition-colors"
      >
        ログアウト
      </button>
    </div>
  </header>
</template>
