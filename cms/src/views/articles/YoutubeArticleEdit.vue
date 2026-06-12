<script setup lang="ts">
import { reactive, ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import TinyMceEditor from '@/components/TinyMceEditor.vue'
import { useArticlesStore } from '@/stores/articles'
import { useCategoriesStore } from '@/stores/categories'
import { useAuthStore } from '@/stores/auth'
import { articlesApi, uploadApi } from '@/api/client'
import type { ArticleStatus, Article } from '@/types'

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
const thumbnailUploading = ref(false)
const thumbnailError = ref('')
const thumbnailInput = ref<HTMLInputElement | null>(null)

const form = reactive({
  title: '',
  slug: '',
  content: '',
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

// Related articles
const relatedArticles = ref<Article[]>([])
const relatedSearchQuery = ref('')
const relatedSearchResults = ref<Article[]>([])
const relatedSearching = ref(false)

async function searchRelatedArticles() {
  const q = relatedSearchQuery.value.trim()
  if (!q) {
    relatedSearchResults.value = []
    return
  }
  relatedSearching.value = true
  try {
    const currentId = isEdit.value ? Number(route.params.id) : null
    if (/^\d+$/.test(q)) {
      const article = await articlesApi.get(Number(q)).catch(() => null)
      relatedSearchResults.value = article && article.id !== currentId ? [article] : []
    } else {
      const res = await articlesApi.list({ keyword: q, per_page: 10 })
      relatedSearchResults.value = res.items.filter(a => a.id !== currentId)
    }
  } finally {
    relatedSearching.value = false
  }
}

function addRelatedArticle(article: Article) {
  if (relatedArticles.value.length >= 3) return
  if (relatedArticles.value.some(a => a.id === article.id)) return
  relatedArticles.value.push(article)
  relatedSearchResults.value = []
  relatedSearchQuery.value = ''
}

function removeRelatedArticle(id: number) {
  relatedArticles.value = relatedArticles.value.filter(a => a.id !== id)
}

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

function isoToInputDate(iso: string): string {
  if (!iso) return ''
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
    if (!form.eye_catch_image) {
      form.eye_catch_image = data.thumbnail
    }
    if (!form.title && data.title) {
      form.title = data.title
      autoSlug()
    }
    if (!form.published_at && data.published_at) {
      form.published_at = isoToInputDate(data.published_at)
    }
  } catch {
    importError.value = '動画情報の取得に失敗しました。URLまたはIDを確認してください。'
  } finally {
    importLoading.value = false
  }
}

async function handleThumbnailChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  thumbnailUploading.value = true
  thumbnailError.value = ''
  try {
    const result = await uploadApi.upload(file)
    form.eye_catch_image = result.url
  } catch {
    thumbnailError.value = '画像のアップロードに失敗しました'
  } finally {
    thumbnailUploading.value = false
    input.value = ''
  }
}

function removeThumbnail() {
  form.eye_catch_image = ''
}

async function handleSubmit() {
  saving.value = true
  errorMsg.value = ''
  try {
    const publishedAt = inputToDbDate(form.published_at)
    const relatedIds = relatedArticles.value.map(a => a.id)
    const payload: Record<string, unknown> = {
      title: form.title,
      slug: form.slug,
      content: form.content,
      eye_catch_image: form.eye_catch_image,
      category_id: form.category_id ?? youtubeCategory.value?.id ?? null,
      status: form.status,
      youtube_url: form.youtube_url,
      youtube_video_id: form.youtube_video_id,
      youtube_thumbnail: form.youtube_thumbnail,
      author_id: auth.user?.id ?? 0,
    }
    if (relatedIds.length > 0) {
      payload.related_article_ids = JSON.stringify(relatedIds)
    } else if (isEdit.value && articlesStore.current?.related_article_ids != null) {
      payload.related_article_ids = '[]'
    }
    if (publishedAt !== null) {
      payload.published_at = publishedAt
    }
    if (isEdit.value) {
      await articlesStore.update(Number(route.params.id), payload as unknown as Parameters<typeof articlesStore.update>[1])
    } else {
      await articlesStore.create(payload as unknown as Parameters<typeof articlesStore.create>[0])
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
      form.eye_catch_image = a.eye_catch_image ?? ''
      form.category_id = a.category_id
      form.status = a.status
      form.published_at = dbToInputDate(a.published_at)
      form.youtube_url = a.youtube_url ?? ''
      form.youtube_video_id = a.youtube_video_id ?? ''
      form.youtube_thumbnail = a.youtube_thumbnail ?? ''
      youtubeInput.value = a.youtube_url ?? ''
      slugManuallyEdited = true

      if (a.related_article_ids) {
        try {
          const ids: number[] = JSON.parse(a.related_article_ids)
          if (Array.isArray(ids) && ids.length > 0) {
            const fetched = await Promise.all(
              ids.slice(0, 3).map(id => articlesApi.get(id).catch(() => null))
            )
            relatedArticles.value = fetched.filter((x): x is Article => x !== null)
          }
        } catch {
          // ignore parse errors
        }
      }
    }
  }
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

    <!-- メインエリア: 縦スクロール -->
    <div class="flex-1 overflow-y-auto bg-gray-50">
      <div class="max-w-4xl mx-auto px-6 py-6 space-y-5">

        <!-- YouTube情報 -->
        <section class="bg-white rounded-lg border border-gray-200 p-5 space-y-4">
          <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">YouTube動画</h3>

          <!-- YouTube URL インポート -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">YouTube URL または 動画ID</label>
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

          <!-- 動画プレビュー -->
          <div v-if="form.youtube_video_id" class="bg-gray-50 rounded-md p-3 space-y-2">
            <img
              v-if="form.youtube_thumbnail"
              :src="form.youtube_thumbnail"
              :alt="form.title"
              class="w-full max-w-sm rounded object-cover"
            />
            <p class="text-xs text-gray-500 font-mono">ID: {{ form.youtube_video_id }}</p>
          </div>
        </section>

        <!-- 記事情報 -->
        <section class="bg-white rounded-lg border border-gray-200 p-5 space-y-4">
          <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">記事情報</h3>

          <!-- タイトル -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">タイトル <span class="text-red-500">*</span></label>
            <input
              v-model="form.title"
              @input="autoSlug"
              required
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
              placeholder="記事タイトル"
            />
          </div>

          <!-- スラッグ -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">スラッグ <span class="text-red-500">*</span></label>
            <input
              v-model="form.slug"
              required
              pattern="[a-z0-9\-]+"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 font-mono"
              placeholder="article-slug"
            />
            <p class="text-xs text-gray-400 mt-0.5">半角英数字・ハイフンのみ</p>
          </div>

          <!-- カテゴリ・ステータス・公開日時 -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">カテゴリ</label>
              <select v-model="form.category_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none">
                <option :value="youtubeCategory?.id ?? null">
                  {{ youtubeCategory?.name ?? 'YouTube' }}
                </option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">ステータス</label>
              <select v-model="form.status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none">
                <option value="draft">下書き</option>
                <option value="published">公開</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">公開日時</label>
              <input
                v-model="form.published_at"
                type="datetime-local"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
              <p class="text-xs text-gray-400 mt-0.5">空欄の場合は保存時に自動設定</p>
            </div>
          </div>

          <!-- サムネイル -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">サムネイル</label>
            <input
              ref="thumbnailInput"
              type="file"
              accept="image/jpeg,image/png,image/gif,image/webp"
              class="hidden"
              @change="handleThumbnailChange"
            />
            <div v-if="form.eye_catch_image" class="mt-1 relative inline-block">
              <img :src="form.eye_catch_image" alt="サムネイル" class="rounded border border-gray-200 object-cover h-40 w-auto max-w-xs" />
              <button
                type="button"
                @click="removeThumbnail"
                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs leading-none hover:bg-red-600"
              >✕</button>
            </div>
            <div v-else class="mt-1 w-48 h-28 bg-gray-100 border border-dashed border-gray-300 rounded flex items-center justify-center text-xs text-gray-400">
              NO IMAGE
            </div>
            <div class="mt-2 flex items-center gap-2">
              <button
                type="button"
                :disabled="thumbnailUploading"
                @click="thumbnailInput?.click()"
                class="px-3 py-1.5 text-xs bg-gray-100 border border-gray-300 rounded hover:bg-gray-200 disabled:opacity-50"
              >
                {{ thumbnailUploading ? 'アップロード中…' : '画像を選択' }}
              </button>
              <span v-if="thumbnailError" class="text-xs text-red-500">{{ thumbnailError }}</span>
            </div>
          </div>
        </section>

        <!-- 説明文（TinyMCE） -->
        <section class="bg-white rounded-lg border border-gray-200 p-5">
          <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">説明文・補足テキスト</h3>
          <TinyMceEditor v-model="form.content" :height="400" />
        </section>

        <!-- 関連記事 -->
        <section class="bg-white rounded-lg border border-gray-200 p-5 space-y-4">
          <div>
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">関連記事</h3>
            <p class="text-xs text-gray-400 mt-0.5">最大3件まで設定できます（任意）</p>
          </div>

          <!-- 設定済み関連記事 -->
          <div v-if="relatedArticles.length > 0" class="space-y-2">
            <div
              v-for="article in relatedArticles"
              :key="article.id"
              class="flex items-center justify-between gap-3 px-3 py-2 bg-gray-50 border border-gray-200 rounded-md"
            >
              <div class="flex items-center gap-2 min-w-0">
                <span class="text-xs text-gray-400 font-mono flex-shrink-0">ID:{{ article.id }}</span>
                <span class="text-sm text-gray-700 truncate">{{ article.title }}</span>
              </div>
              <button
                type="button"
                @click="removeRelatedArticle(article.id)"
                class="flex-shrink-0 text-xs text-red-500 hover:text-red-700"
              >削除</button>
            </div>
          </div>

          <!-- 検索フォーム -->
          <div v-if="relatedArticles.length < 3">
            <label class="block text-xs font-semibold text-gray-600 mb-1">記事を検索（IDまたはタイトル）</label>
            <div class="flex gap-2">
              <input
                v-model="relatedSearchQuery"
                type="text"
                placeholder="記事IDまたはタイトルを入力"
                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                @keydown.enter.prevent="searchRelatedArticles"
              />
              <button
                type="button"
                @click="searchRelatedArticles"
                :disabled="relatedSearching"
                class="px-4 py-2 text-sm bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 disabled:opacity-50"
              >{{ relatedSearching ? '検索中…' : '検索' }}</button>
            </div>

            <ul v-if="relatedSearchResults.length > 0" class="mt-2 border border-gray-200 rounded-md divide-y divide-gray-100 bg-white shadow-sm">
              <li
                v-for="result in relatedSearchResults"
                :key="result.id"
                class="flex items-center justify-between gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer"
                @click="addRelatedArticle(result)"
              >
                <div class="flex items-center gap-2 min-w-0">
                  <span class="text-xs text-gray-400 font-mono flex-shrink-0">ID:{{ result.id }}</span>
                  <span class="text-sm text-gray-700 truncate">{{ result.title }}</span>
                </div>
                <span class="flex-shrink-0 text-xs text-blue-500">追加</span>
              </li>
            </ul>
            <p v-else-if="!relatedSearching && relatedSearchQuery && relatedSearchResults.length === 0"
               class="mt-1 text-xs text-gray-400">該当する記事が見つかりませんでした</p>
          </div>
        </section>

      </div>
    </div>
  </div>
</template>
