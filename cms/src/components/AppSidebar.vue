<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()

const navItems = [
  { name: 'Dashboard', label: 'ダッシュボード', icon: '🏠', to: '/dashboard' },
  { name: 'ArticlesList', label: '記事管理', icon: '📝', to: '/articles' },
  { name: 'Categories', label: 'カテゴリ管理', icon: '🗂️', to: '/categories' },
  { name: 'Users', label: 'ユーザー管理', icon: '👤', to: '/users' },
  { name: 'Profile', label: 'プロフィール設定', icon: '⚙️', to: '/profile' },
]

function isActive(name: string): boolean {
  const routeName = String(route.name ?? '')
  if (name === 'ArticlesList') {
    return routeName.startsWith('Article') || routeName.startsWith('Youtube')
  }
  return routeName === name
}
</script>

<template>
  <aside class="w-60 bg-slate-800 text-white flex flex-col flex-shrink-0">
    <div class="px-6 py-4 border-b border-slate-700">
      <span class="text-xl font-bold tracking-wide">Himatsudo</span>
      <span class="block text-xs text-slate-400 mt-0.5">CMS</span>
    </div>
    <nav class="flex-1 py-4 overflow-y-auto">
      <ul class="space-y-1 px-3">
        <li v-for="item in navItems" :key="item.name">
          <RouterLink
            :to="item.to"
            class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            :class="isActive(item.name)
              ? 'bg-slate-700 text-white'
              : 'text-slate-300 hover:bg-slate-700 hover:text-white'"
          >
            <span class="text-base">{{ item.icon }}</span>
            {{ item.label }}
          </RouterLink>
        </li>
      </ul>
    </nav>
  </aside>
</template>
