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

    <!-- メインエリア: 設定パネル(左) + エディタ(右) -->
    <div class="flex flex-1 overflow-hidden">
      <!-- 左: 設定パネル -->
      <aside class="w-72 flex-shrink-0 overflow-y-auto border-r border-gray-200 bg-white p-4 space-y-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">タイトル <span class="text-red-500">*</span></label>
          <input
            v-model="form.title"
            @input="autoSlug"
            required
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
            placeholder="記事タイトル"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">スラッグ <span class="text-red-500">*</span></label>
          <input
            v-model="form.slug"
            required
            pattern="[a-z0-9\-]+"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 font-mono"
            placeholder="article-slug"
          />
          <p class="text-xs text-gray-400 mt-0.5">半角英数字・ハイフンのみ</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">カテゴリ</label>
          <select
            v-model="form.category_id"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none"
          >
            <option :value="null">カテゴリなし</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">ステータス</label>
          <select
            v-model="form.status"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none"
          >
            <option value="draft">下書き</option>
            <option value="published">公開</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">公開日時</label>
          <input
            v-model="form.published_at"
            type="datetime-local"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
          <p class="text-xs text-gray-400 mt-0.5">空欄の場合は保存時に自動設定</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">アイキャッチ画像 URL</label>
          <input
            v-model="form.eye_catch_image"
            type="url"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
            placeholder="https://..."
          />
          <div v-if="form.eye_catch_image" class="mt-2">
            <img :src="form.eye_catch_image" alt="プレビュー" class="w-full rounded border border-gray-200 object-cover max-h-32" />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1 uppercase tracking-wide">抜粋</label>
          <textarea
            v-model="form.excerpt"
            rows="3"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
            placeholder="記事の概要（一覧ページに表示）"
          />
        </div>
      </aside>

      <!-- 右: TinyMCEエディタ -->
      <main class="flex-1 overflow-hidden bg-gray-50 p-4 flex flex-col">
        <label class="block text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">記事本文</label>
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

<script setup lang="ts">
import { reactive, ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import TinyMceEditor from '@/components/TinyMceEditor.vue'
import { useArticlesStore } from '@/stores/articles'
import { useCategoriesStore } from '@/stores/categories'
import { useAuthStore } from '@/stores/auth'
import type { ArticleStatus } from '@/types'

const route = useRoute()
const router = useRouter()
const articlesStore = useArticlesStore()
const categoriesStore = useCategoriesStore()
const auth = useAuthStore()

const isEdit = computed(() => !!route.params.id)
const saving = ref(false)
const errorMsg = ref('')
const editorHeight = ref(600)

const form = reactive({
  title: '',
  slug: '',
  content: '',
  excerpt: '',
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

function updateEditorHeight() {
  // AppHeader(~57px) + edit header bar(~52px) + label(~32px) + padding(~32px) = ~173px
  editorHeight.value = Math.max(400, window.innerHeight - 180)
}

async function handleSubmit(overrideStatus?: ArticleStatus) {
  saving.value = true
  errorMsg.value = ''
  try {
    const publishedAt = inputToDbDate(form.published_at)
    const payload: Record<string, unknown> = {
      title: form.title,
      slug: form.slug,
      content: form.content,
      blocks: '',
      excerpt: form.excerpt,
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

function saveDraft() {
  handleSubmit('draft')
}

onMounted(async () => {
  updateEditorHeight()
  window.addEventListener('resize', updateEditorHeight)

  await categoriesStore.fetchAll()
  if (isEdit.value) {
    await articlesStore.fetchOne(Number(route.params.id))
    const a = articlesStore.current
    if (a) {
      form.title = a.title
      form.slug = a.slug
      form.excerpt = a.excerpt ?? ''
      form.eye_catch_image = a.eye_catch_image ?? ''
      form.category_id = a.category_id
      form.status = a.status
      form.published_at = dbToInputDate(a.published_at)
      form.content = a.content ?? ''
      slugManuallyEdited = true
    }
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', updateEditorHeight)
})
</script>
