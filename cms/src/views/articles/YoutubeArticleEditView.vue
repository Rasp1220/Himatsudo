<template>
  <div class="max-w-2xl">
    <div class="flex items-center gap-4 mb-6">
      <RouterLink to="/articles" class="text-gray-400 hover:text-gray-600 text-sm">
        &larr; 記事一覧
      </RouterLink>
      <h2 class="text-xl font-bold text-gray-800">
        {{ isEdit ? 'YouTube記事を編集' : 'YouTube記事を新規作成' }}
      </h2>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-5">
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 space-y-4">
        <!-- YouTube URL input -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">YouTube URL または 動画ID</label>
          <div class="flex gap-2">
            <input
              v-model="youtubeInput"
              class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
              placeholder="https://www.youtube.com/watch?v=... または dQw4w9WgXcQ"
            />
            <button
              type="button"
              @click="importYoutube"
              :disabled="importLoading"
              class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700 disabled:opacity-50 whitespace-nowrap"
            >
              {{ importLoading ? '取得中…' : '動画情報を取得' }}
            </button>
          </div>
          <p v-if="importError" class="text-xs text-red-500 mt-1">{{ importError }}</p>
        </div>

        <!-- Preview -->
        <div v-if="form.youtube_video_id" class="flex gap-4 items-start bg-gray-50 rounded-md p-3">
          <img
            v-if="form.youtube_thumbnail"
            :src="form.youtube_thumbnail"
            :alt="form.title"
            class="w-32 rounded object-cover flex-shrink-0"
          />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-700 truncate">{{ form.title || '（タイトル未設定）' }}</p>
            <p class="text-xs text-gray-400 mt-0.5 font-mono">ID: {{ form.youtube_video_id }}</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">タイトル <span class="text-red-500">*</span></label>
          <input
            v-model="form.title"
            @input="autoSlug"
            required
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">スラッグ <span class="text-red-500">*</span></label>
          <input
            v-model="form.slug"
            required
            pattern="[a-z0-9\-]+"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 font-mono"
          />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">カテゴリ</label>
            <select v-model="form.category_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
              <option :value="youtubeCategory?.id ?? null">
                {{ youtubeCategory?.name ?? 'YouTube' }}
              </option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
            <select v-model="form.status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
              <option value="draft">下書き</option>
              <option value="published">公開</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">説明文</label>
          <textarea
            v-model="form.content"
            rows="5"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 resize-y"
            placeholder="動画の説明・補足テキスト"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">抜粋</label>
          <textarea
            v-model="form.excerpt"
            rows="2"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none resize-none"
            placeholder="一覧ページに表示される概要"
          />
        </div>
      </div>

      <p v-if="errorMsg" class="text-sm text-red-600 bg-red-50 rounded px-3 py-2">{{ errorMsg }}</p>

      <div class="flex gap-3">
        <button
          type="submit"
          :disabled="saving"
          class="px-6 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          {{ saving ? '保存中…' : (isEdit ? '更新する' : '作成する') }}
        </button>
        <RouterLink to="/articles" class="px-4 py-2 text-sm text-gray-600 hover:underline">
          キャンセル
        </RouterLink>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useArticlesStore } from '@/stores/articles'
import { useCategoriesStore } from '@/stores/categories'
import { useAuthStore } from '@/stores/auth'
import { articlesApi } from '@/api/client'
import type { ArticleStatus } from '@/types'

const route = useRoute()
const router = useRouter()
const articlesStore = useArticlesStore()
const categoriesStore = useCategoriesStore()
const auth = useAuthStore()

const isEdit = computed(() => !!route.params.id)
const saving = ref(false)
const errorMsg = ref('')
const youtubeInput = ref('')
const importLoading = ref(false)
const importError = ref('')

const form = reactive({
  title: '',
  slug: '',
  content: '',
  excerpt: '',
  eye_catch_image: '',
  category_id: null as number | null,
  status: 'draft' as ArticleStatus,
  youtube_url: '',
  youtube_video_id: '',
  youtube_thumbnail: '',
})

const { items: categories } = storeToRefs(categoriesStore)
const youtubeCategory = computed(() => categories.value.find((c) => c.type === 'youtube'))

let slugManuallyEdited = false

function autoSlug() {
  if (!slugManuallyEdited && !isEdit.value) {
    form.slug = form.title
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^a-z0-9\-]/g, '')
      .slice(0, 80)
  }
}

async function importYoutube() {
  if (!youtubeInput.value.trim()) return
  importLoading.value = true
  importError.value = ''
  try {
    const data = await articlesApi.importYoutube(youtubeInput.value.trim())
    form.youtube_video_id = data.video_id
    form.youtube_url = data.youtube_url
    form.youtube_thumbnail = data.thumbnail
    if (!form.title && data.title) {
      form.title = data.title
      autoSlug()
    }
  } catch {
    importError.value = '動画情報の取得に失敗しました。URLまたはIDを確認してください。'
  } finally {
    importLoading.value = false
  }
}

async function handleSubmit() {
  saving.value = true
  errorMsg.value = ''
  try {
    const payload = {
      ...form,
      author_id: auth.user?.id ?? 0,
      category_id: form.category_id ?? youtubeCategory.value?.id ?? null,
    }
    if (isEdit.value) {
      await articlesStore.update(Number(route.params.id), payload)
    } else {
      await articlesStore.create(payload)
    }
    router.push('/articles')
  } catch {
    errorMsg.value = '保存に失敗しました。入力内容を確認してください。'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await categoriesStore.fetchAll()
  form.category_id = youtubeCategory.value?.id ?? null
  if (isEdit.value) {
    await articlesStore.fetchOne(Number(route.params.id))
    const a = articlesStore.current
    if (a) {
      form.title = a.title
      form.slug = a.slug
      form.content = a.content ?? ''
      form.excerpt = a.excerpt ?? ''
      form.category_id = a.category_id
      form.status = a.status
      form.youtube_url = a.youtube_url ?? ''
      form.youtube_video_id = a.youtube_video_id ?? ''
      form.youtube_thumbnail = a.youtube_thumbnail ?? ''
      youtubeInput.value = a.youtube_url ?? ''
      slugManuallyEdited = true
    }
  }
})
</script>
