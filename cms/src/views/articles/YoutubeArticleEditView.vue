<script setup lang="ts">
import { reactive, ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import TinyMceEditor from '@/components/TinyMceEditor.vue'
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
const editorHeight = ref(600)
const publishedAtFromYoutube = ref(false)
const excerptFromYoutube = ref(false)

const form = reactive({
  title: '',
  slug: '',
  content: '',
  excerpt: '',
  eye_catch_image: '',
  category_id: null as number | null,
  status: 'draft' as ArticleStatus,
  published_at: '',
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

function dbToInputDate(dbDate: string | null | undefined): string {
  if (!dbDate) return ''
  return dbDate.replace(' ', 'T').substring(0, 16)
}

function inputToDbDate(inputDate: string): string | null {
  if (!inputDate) return null
  return inputDate.replace('T', ' ') + ':00'
}

function updateEditorHeight() {
  editorHeight.value = Math.max(400, window.innerHeight - 180)
}

function isoToInputDate(iso: string): string {
  if (!iso) return ''
  // ISO 8601 "2023-01-15T10:00:00Z" -> "2023-01-15T10:00"
  return iso.substring(0, 16)
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
    if (!form.excerpt && data.description) {
      form.excerpt = data.description.slice(0, 200)
      excerptFromYoutube.value = true
    }
    if (!form.published_at && data.published_at) {
      form.published_at = isoToInputDate(data.published_at)
      publishedAtFromYoutube.value = true
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
    const publishedAt = inputToDbDate(form.published_at)
    const payload: Record<string, unknown> = {
      title: form.title,
      slug: form.slug,
      content: form.content,
      excerpt: form.excerpt,
      eye_catch_image: form.eye_catch_image,
      category_id: form.category_id ?? youtubeCategory.value?.id ?? null,
      status: form.status,
      youtube_url: form.youtube_url,
      youtube_video_id: form.youtube_video_id,
      youtube_thumbnail: form.youtube_thumbnail,
      author_id: auth.user?.id ?? 0,
    }
    if (publishedAt !== null) {
      payload.published_at = publishedAt
    }
    if (isEdit.value) {
      await articlesStore.update(Number(route.params.id), payload as Parameters<typeof articlesStore.update>[1])
    } else {
      await articlesStore.create(payload as Parameters<typeof articlesStore.create>[0])
    }
    router.push('/articles')
  } catch {
    errorMsg.value = '保存に失敗しました。入力内容を確認してください。'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  updateEditorHeight()
  window.addEventListener('resize', updateEditorHeight)

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
      form.published_at = dbToInputDate(a.published_at)
      form.youtube_url = a.youtube_url ?? ''
      form.youtube_video_id = a.youtube_video_id ?? ''
      form.youtube_thumbnail = a.youtube_thumbnail ?? ''
      youtubeInput.value = a.youtube_url ?? ''
      slugManuallyEdited = true
    }
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', updateEditorHeight)
})
</script>

<template>
  <div class="flex flex-col h-full">
    <!-- ヘッダーバー -->
    <div class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200 flex-shrink-0">
      <div class="flex items-center gap-3">
        <RouterLink to="/articles" class="text-gray-400 hover:text-gray-600 text-sm">
          &larr; 記事一覧
        </RouterLink>
        <span class="text-gray-300">|</span>
        <h2 class="text-base font-bold text-gray-800">
          {{ isEdit ? 'YouTube記事を編集' : 'YouTube記事を新規作成' }}
        </h2>
      </div>
      <div class="flex items-center gap-2">
        <p v-if="errorMsg" class="text-sm text-red-600 mr-3">{{ errorMsg }}</p>
        <button
          type="button"
          @click="handleSubmit"
          :disabled="saving"
          class="px-5 py-1.5 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          {{ saving ? '保存中…' : (isEdit ? '更新する' : '作成する') }}
        </button>
        <RouterLink to="/articles" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700">
          キャンセル
        </RouterLink>
      </div>
    </div>

    <!-- メインエリア -->
    <div class="flex flex-1 overflow-hidden">
      <!-- 左: 設定パネル -->
      <aside class="w-80 flex-shrink-0 overflow-y-auto border-r border-gray-200 bg-white p-4 space-y-4">
        <!-- YouTube URL -->
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">YouTube URL または 動画ID</label>
          <div class="flex gap-2">
            <input
              v-model="youtubeInput"
              class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
              placeholder="https://www.youtube.com/watch?v=..."
            />
            <button
              type="button"
              @click="importYoutube"
              :disabled="importLoading"
              class="px-3 py-2 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700 disabled:opacity-50 whitespace-nowrap"
            >
              {{ importLoading ? '取得中…' : '取得' }}
            </button>
          </div>
          <p v-if="importError" class="text-xs text-red-500 mt-1">{{ importError }}</p>
        </div>

        <!-- プレビュー -->
        <div v-if="form.youtube_video_id" class="bg-gray-50 rounded-md p-3 space-y-2">
          <img
            v-if="form.youtube_thumbnail"
            :src="form.youtube_thumbnail"
            :alt="form.title"
            class="w-full rounded object-cover"
          />
          <p class="text-xs text-gray-500 font-mono">ID: {{ form.youtube_video_id }}</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">タイトル <span class="text-red-500">*</span></label>
          <input
            v-model="form.title"
            @input="autoSlug"
            required
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">スラッグ <span class="text-red-500">*</span></label>
          <input
            v-model="form.slug"
            required
            pattern="[a-z0-9\-]+"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 font-mono"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">カテゴリ</label>
          <select v-model="form.category_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
            <option :value="youtubeCategory?.id ?? null">
              {{ youtubeCategory?.name ?? 'YouTube' }}
            </option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">ステータス</label>
          <select v-model="form.status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
            <option value="draft">下書き</option>
            <option value="published">公開</option>
          </select>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">公開日時</label>
            <span v-if="publishedAtFromYoutube" class="text-xs text-red-500 font-medium">YouTube投稿日</span>
          </div>
          <input
            v-model="form.published_at"
            type="datetime-local"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
          <p class="text-xs text-gray-400 mt-0.5">空欄の場合は保存時に自動設定</p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">抜粋</label>
            <span v-if="excerptFromYoutube" class="text-xs text-red-500 font-medium">YouTube説明文</span>
          </div>
          <textarea
            v-model="form.excerpt"
            rows="3"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none resize-none"
            placeholder="一覧ページに表示される概要"
          />
        </div>
      </aside>

      <!-- 右: 説明文 TinyMCE -->
      <main class="flex-1 overflow-hidden bg-gray-50 p-4 flex flex-col">
        <label class="block text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">説明文・補足テキスト</label>
        <div class="flex-1 min-h-0">
          <TinyMceEditor
            v-model="form.content"
            :height="editorHeight"
          />
        </div>
      </main>
    </div>
  </div>
</template>
