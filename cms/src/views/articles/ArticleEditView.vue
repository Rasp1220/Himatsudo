<template>
  <div class="max-w-3xl">
    <div class="flex items-center gap-4 mb-6">
      <RouterLink to="/articles" class="text-gray-400 hover:text-gray-600 text-sm">
        &larr; 記事一覧
      </RouterLink>
      <h2 class="text-xl font-bold text-gray-800">
        {{ isEdit ? '記事を編集' : '記事を新規作成' }}
      </h2>
    </div>

    <form @submit.prevent="() => handleSubmit()" class="space-y-5">
      <!-- Meta fields -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 space-y-4">
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
            placeholder="article-slug"
          />
          <p class="text-xs text-gray-400 mt-1">半角英数字とハイフンのみ</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">カテゴリ</label>
            <select
              v-model="form.category_id"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none"
            >
              <option :value="null">カテゴリなし</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
            <select
              v-model="form.status"
              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none"
            >
              <option value="draft">下書き</option>
              <option value="published">公開</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">アイキャッチ画像 URL</label>
          <input
            v-model="form.eye_catch_image"
            type="url"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
            placeholder="https://..."
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">抜粋</label>
          <textarea
            v-model="form.excerpt"
            rows="2"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
            placeholder="記事の概要（省略可）"
          />
        </div>
      </div>

      <!-- Block editor -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
        <label class="block text-sm font-medium text-gray-700 mb-3">記事コンテンツ</label>
        <BlockEditor v-model="form.blocks" />
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
        <button
          v-if="!isEdit"
          type="button"
          @click="saveDraft"
          :disabled="saving"
          class="px-6 py-2 bg-gray-500 text-white text-sm font-semibold rounded-md hover:bg-gray-600 disabled:opacity-50"
        >
          下書き保存
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
import BlockEditor from '@/components/blocks/BlockEditor.vue'
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

const form = reactive({
  title: '',
  slug: '',
  blocks: '[]',
  excerpt: '',
  eye_catch_image: '',
  category_id: null as number | null,
  status: 'draft' as ArticleStatus,
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

async function handleSubmit(overrideStatus?: ArticleStatus) {
  saving.value = true
  errorMsg.value = ''
  try {
    const payload = {
      title: form.title,
      slug: form.slug,
      blocks: form.blocks,
      content: '',
      excerpt: form.excerpt,
      eye_catch_image: form.eye_catch_image,
      category_id: form.category_id ?? 0,
      status: overrideStatus ?? form.status,
      youtube_url: '',
      youtube_video_id: '',
      youtube_thumbnail: '',
      author_id: auth.user?.id ?? 0,
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
      form.excerpt = a.excerpt ?? ''
      form.eye_catch_image = a.eye_catch_image ?? ''
      form.category_id = a.category_id
      form.status = a.status
      form.blocks = a.blocks ?? '[]'
      slugManuallyEdited = true
    }
  }
})
</script>
