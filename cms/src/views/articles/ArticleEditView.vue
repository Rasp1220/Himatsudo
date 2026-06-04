<script setup lang="ts">
import { reactive, ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import BlockEditor from '@/components/blocks/BlockEditor.vue'
import { useArticlesStore } from '@/stores/articles'
import { useCategoriesStore } from '@/stores/categories'
import { useAuthStore } from '@/stores/auth'
import { uploadApi } from '@/api/client'
import type { ArticleStatus } from '@/types'

const route = useRoute()
const router = useRouter()
const articlesStore = useArticlesStore()
const categoriesStore = useCategoriesStore()
const auth = useAuthStore()

const isEdit = computed(() => !!route.params.id)

// アップロードされた画像はルート相対パス (/uploads/...) で返るため、
// CMS が別ポートで動いている場合も正しく表示できるよう backend origin を付与する
function resolveUploadUrl(url: string): string {
  if (!url || url.startsWith('http')) return url
  const apiBase = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8080/admin/api'
  try {
    return new URL(apiBase).origin + url
  } catch {
    return url
  }
}
const saving = ref(false)
const errorMsg = ref('')
const thumbnailUploading = ref(false)
const thumbnailError = ref('')
const thumbnailInput = ref<HTMLInputElement | null>(null)
const showValidation = ref(false)

// バリデーション失敗フィールドの判定（保存試行後のみ赤枠を表示）
function isInvalid(value: string): boolean {
  return showValidation.value && !value.trim()
}

const form = reactive({
  title: '',
  slug: '',
  blocks: '',
  eye_catch_image: '',
  category_id: null as number | null,
  status: 'draft' as ArticleStatus,
  published_at: '',
})

const { items: categories } = storeToRefs(categoriesStore)

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

async function handleSubmit(overrideStatus?: ArticleStatus) {
  showValidation.value = true
  if (!form.title.trim() || !form.slug.trim() || !form.category_id) {
    errorMsg.value = '必須項目を入力してください。'
    return
  }
  saving.value = true
  errorMsg.value = ''
  try {
    const publishedAt = inputToDbDate(form.published_at)
    const payload: Record<string, unknown> = {
      title: form.title,
      slug: form.slug,
      content: '',
      blocks: form.blocks,
      eye_catch_image: form.eye_catch_image,
      category_id: form.category_id ?? 0,
      status: overrideStatus ?? form.status,
      youtube_url: '',
      youtube_video_id: '',
      youtube_thumbnail: '',
      author_id: auth.user?.id ?? 0,
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

function saveDraft() {
  handleSubmit('draft')
}

onMounted(async () => {
  await categoriesStore.fetchAll()
  if (isEdit.value) {
    await articlesStore.fetchOne(Number(route.params.id))
    const a = articlesStore.current
    if (a) {
      form.title = a.title
      form.slug = a.slug
      form.eye_catch_image = a.eye_catch_image ?? ''
      form.category_id = a.category_id
      form.status = a.status
      form.published_at = dbToInputDate(a.published_at)
      form.blocks = a.blocks ?? ''
      slugManuallyEdited = true
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
          {{ isEdit ? '記事を編集' : '記事を新規作成' }}
        </h2>
      </div>
      <div class="flex items-center gap-2">
        <p v-if="errorMsg" class="text-sm text-red-600 mr-3">{{ errorMsg }}</p>
        <button
          v-if="!isEdit"
          type="button"
          @click="saveDraft"
          :disabled="saving"
          class="px-4 py-1.5 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 disabled:opacity-50"
        >
          下書き保存
        </button>
        <button
          type="button"
          @click="() => handleSubmit()"
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
              :class="[
                'w-full px-3 py-2 text-sm border rounded-md focus:outline-none focus:ring-2',
                isInvalid(form.title)
                  ? 'border-red-400 focus:ring-red-300 bg-red-50'
                  : 'border-gray-300 focus:ring-blue-400',
              ]"
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
              :class="[
                'w-full px-3 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 font-mono',
                isInvalid(form.slug)
                  ? 'border-red-400 focus:ring-red-300 bg-red-50'
                  : 'border-gray-300 focus:ring-blue-400',
              ]"
              placeholder="article-slug"
            />
            <p class="text-xs text-gray-400 mt-0.5">半角英数字・ハイフンのみ</p>
          </div>

          <!-- カテゴリ・ステータス・公開日時 -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">カテゴリ <span class="text-red-500">*</span></label>
              <select
                v-model="form.category_id"
                :class="[
                  'w-full px-3 py-2 text-sm border rounded-md focus:outline-none',
                  showValidation && !form.category_id
                    ? 'border-red-400 bg-red-50'
                    : 'border-gray-300',
                ]"
              >
                <option :value="null" disabled>カテゴリを選択してください</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">ステータス</label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none"
              >
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
              <img :src="resolveUploadUrl(form.eye_catch_image)" alt="サムネイル" class="rounded border border-gray-200 object-cover h-40 w-auto max-w-xs" />
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

        <!-- 記事本文（ブロックエディタ） -->
        <section class="bg-white rounded-lg border border-gray-200 p-5">
          <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">記事本文</h3>
          <BlockEditor v-model="form.blocks" />
        </section>

      </div>
    </div>
  </div>
</template>
