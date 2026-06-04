<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useArticlesStore } from '@/stores/articles'
import { useCategoriesStore } from '@/stores/categories'
import { articlesApi } from '@/api/client'
import type { Article } from '@/types'
import DataTable from '@/components/ui/DataTable.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'

const articles = useArticlesStore()
const categoriesStore = useCategoriesStore()
const { items: categories } = storeToRefs(categoriesStore)

const filters = reactive<{
  keyword: string
  category_id: number | null
  status: string | null
}>({ keyword: '', category_id: null, status: null })

const showDeleteModal = ref(false)
const deleteTarget = ref<Article | null>(null)

const columns = [
  { key: 'title', label: 'タイトル' },
  { key: 'thumbnail', label: 'サムネイル', width: '72px' },
  { key: 'category_name', label: 'カテゴリ', width: '140px' },
  { key: 'status', label: 'ステータス', width: '100px' },
  { key: 'published_at', label: '公開日', width: '120px' },
  { key: 'actions', label: '', width: '150px' },
]

const duplicating = ref<number | null>(null)

async function duplicateArticle(article: Article) {
  duplicating.value = article.id
  try {
    await articlesApi.duplicate(article.id)
    await fetchPage(articles.pagination.page)
  } finally {
    duplicating.value = null
  }
}

let debounceTimer: ReturnType<typeof setTimeout> | null = null
function debouncedFetch() {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchPage(1), 400)
}

async function fetchPage(page: number) {
  await articles.fetchList({
    page,
    per_page: 20,
    category_id: filters.category_id,
    status: filters.status,
    keyword: filters.keyword || null,
  })
}

function editPath(article: Article): string {
  return article.category_type === 'youtube'
    ? `/articles/youtube/${article.id}/edit`
    : `/articles/${article.id}/edit`
}

function categoryBadgeClass(type: string | null): string {
  if (type === 'youtube') return 'bg-red-100 text-red-700'
  if (type === 'blog') return 'bg-green-100 text-green-700'
  return 'bg-blue-100 text-blue-700'
}

function formatDate(d: string): string {
  return new Date(d).toLocaleDateString('ja-JP')
}

function confirmDelete(article: Article) {
  deleteTarget.value = article
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!deleteTarget.value) return
  await articles.remove(deleteTarget.value.id)
  deleteTarget.value = null
}

onMounted(async () => {
  await Promise.all([fetchPage(1), categoriesStore.fetchAll()])
})
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-800">記事管理</h2>
      <div class="flex gap-2">
        <RouterLink
          to="/articles/youtube/new"
          class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700"
        >
          ▶ YouTube記事
        </RouterLink>
        <RouterLink
          to="/articles/new"
          class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700"
        >
          ＋ 新規作成
        </RouterLink>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-300 p-4 mb-4 flex flex-wrap gap-3">
      <input
        v-model="filters.keyword"
        @input="debouncedFetch"
        type="search"
        placeholder="キーワード検索…"
        class="flex-1 min-w-48 px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
      />
      <select
        v-model="filters.category_id"
        @change="fetchPage(1)"
        class="px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none"
      >
        <option :value="null">すべてのカテゴリ</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
      <select
        v-model="filters.status"
        @change="fetchPage(1)"
        class="px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none"
      >
        <option :value="null">すべてのステータス</option>
        <option value="published">公開</option>
        <option value="draft">下書き</option>
      </select>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-300">
      <DataTable :columns="columns" :rows="articles.items" :loading="articles.loading">
        <template #title="{ row }">
          <RouterLink
            :to="editPath(row as Article)"
            class="font-medium text-blue-700 hover:underline line-clamp-2"
          >{{ (row as Article).title }}</RouterLink>
        </template>
        <template #thumbnail="{ row }">
          <div class="relative overflow-hidden rounded bg-gray-100" style="width:40px;aspect-ratio:3/4">
            <img
              v-if="(row as Article).eye_catch_image || (row as Article).youtube_thumbnail"
              :src="((row as Article).eye_catch_image || (row as Article).youtube_thumbnail)!"
              :alt="(row as Article).title"
              class="absolute inset-0 w-full h-full object-contain"
            />
            <div v-else class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs font-medium">
              NO
            </div>
          </div>
        </template>
        <template #category_name="{ row }">
          <span
            v-if="(row as Article).category_name"
            class="px-2 py-0.5 text-xs font-medium rounded"
            :class="categoryBadgeClass((row as Article).category_type)"
          >
            {{ (row as Article).category_name }}
          </span>
          <span v-else class="text-gray-400 text-xs">—</span>
        </template>
        <template #status="{ row }">
          <span
            class="px-2 py-0.5 text-xs font-medium rounded"
            :class="(row as Article).status === 'published'
              ? 'bg-green-100 text-green-700'
              : 'bg-yellow-100 text-yellow-700'"
          >
            {{ (row as Article).status === 'published' ? '公開' : '下書き' }}
          </span>
        </template>
        <template #published_at="{ row }">
          <span class="text-xs text-gray-500">
            {{ (row as Article).published_at ? formatDate((row as Article).published_at!) : '—' }}
          </span>
        </template>
        <template #actions="{ row }">
          <div class="flex gap-2">
            <RouterLink
              :to="editPath(row as Article)"
              class="text-blue-600 hover:underline text-xs font-medium"
            >
              編集
            </RouterLink>
            <button
              @click="duplicateArticle(row as Article)"
              :disabled="duplicating === (row as Article).id"
              class="text-green-600 hover:underline text-xs font-medium disabled:opacity-50"
            >
              {{ duplicating === (row as Article).id ? '…' : '複製' }}
            </button>
            <button
              @click="confirmDelete(row as Article)"
              class="text-red-500 hover:underline text-xs font-medium"
            >
              削除
            </button>
          </div>
        </template>
      </DataTable>

      <div class="px-4 py-3 border-t border-gray-300 flex items-center justify-between">
        <p class="text-xs text-gray-500">全 {{ articles.pagination.total }} 件</p>
        <Pagination
          :current-page="articles.pagination.page"
          :last-page="articles.pagination.last_page"
          @change="fetchPage"
        />
      </div>
    </div>

    <ConfirmModal
      v-model="showDeleteModal"
      title="記事を削除"
      :message="`「${deleteTarget?.title}」を削除しますか？この操作は取り消せません。`"
      confirm-label="削除する"
      @confirm="executeDelete"
    />
  </div>
</template>
