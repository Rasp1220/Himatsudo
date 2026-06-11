<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { useCategoriesStore } from '@/stores/categories'
import type { Category } from '@/types'
import DataTable from '@/components/ui/DataTable.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'

const categoriesStore = useCategoriesStore()

const columns = [
  { key: 'name', label: 'カテゴリ名' },
  { key: 'slug', label: 'スラッグ' },
  { key: 'type', label: 'タイプ', width: '100px' },
  { key: 'sort_order', label: '表示順', width: '80px' },
  { key: 'actions', label: '', width: '100px' },
]

const showModal = ref(false)
const editTarget = ref<Category | null>(null)
const modalLoading = ref(false)
const modalError = ref('')
const modalForm = reactive({ name: '', slug: '', type: 'custom' as Category['type'], sort_order: 0 })

const showDeleteModal = ref(false)
const deleteTarget = ref<Category | null>(null)

function typeLabel(type: string): string {
  const map: Record<string, string> = { normal: '通常', blog: 'ブログ', youtube: 'YouTube', custom: 'カスタム' }
  return map[type] ?? type
}

function openCreate() {
  editTarget.value = null
  Object.assign(modalForm, { name: '', slug: '', type: 'custom', sort_order: 0 })
  showModal.value = true
}

function openEdit(c: Category) {
  editTarget.value = c
  Object.assign(modalForm, { name: c.name, slug: c.slug, type: c.type, sort_order: c.sort_order })
  showModal.value = true
}

async function handleSave() {
  modalLoading.value = true
  modalError.value = ''
  try {
    if (editTarget.value) {
      await categoriesStore.update(editTarget.value.id, modalForm)
    } else {
      await categoriesStore.create(modalForm)
    }
    showModal.value = false
  } catch {
    modalError.value = '保存に失敗しました。スラッグが重複していないか確認してください。'
  } finally {
    modalLoading.value = false
  }
}

function confirmDelete(c: Category) {
  deleteTarget.value = c
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!deleteTarget.value) return
  await categoriesStore.remove(deleteTarget.value.id)
  deleteTarget.value = null
}

onMounted(() => categoriesStore.fetchAll())
</script>

<template>
  <div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-800">カテゴリ管理</h2>
      <button
        @click="openCreate"
        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700"
      >
        ＋ カテゴリを追加
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
      <DataTable :columns="columns" :rows="categoriesStore.items" :loading="categoriesStore.loading">
        <template #type="{ value }">
          <span class="text-xs px-2 py-0.5 rounded font-medium"
            :class="{
              'bg-blue-100 text-blue-700': value === 'normal',
              'bg-green-100 text-green-700': value === 'blog',
              'bg-red-100 text-red-700': value === 'youtube',
              'bg-gray-100 text-gray-600': value === 'custom',
            }"
          >
            {{ typeLabel(value as string) }}
          </span>
        </template>
        <template #actions="{ row }">
          <div class="flex gap-2">
            <button @click="openEdit(row as Category)" class="text-blue-600 hover:underline text-xs font-medium">編集</button>
            <button @click="confirmDelete(row as Category)" class="text-red-500 hover:underline text-xs font-medium">削除</button>
          </div>
        </template>
      </DataTable>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="showModal = false"
      >
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
          <h3 class="text-lg font-semibold mb-4">{{ editTarget ? 'カテゴリを編集' : 'カテゴリを追加' }}</h3>
          <form @submit.prevent="handleSave" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">カテゴリ名 <span class="text-red-500">*</span></label>
              <input v-model="modalForm.name" required class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">スラッグ <span class="text-red-500">*</span></label>
              <input v-model="modalForm.slug" required pattern="[a-z0-9\-]+" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none font-mono" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">タイプ</label>
              <select v-model="modalForm.type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                <option value="normal">通常記事</option>
                <option value="blog">ブログ</option>
                <option value="youtube">YouTube</option>
                <option value="custom">カスタム</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">表示順</label>
              <input v-model.number="modalForm.sort_order" type="number" min="0" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none" />
            </div>
            <p v-if="modalError" class="text-sm text-red-600">{{ modalError }}</p>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                キャンセル
              </button>
              <button type="submit" :disabled="modalLoading" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                {{ modalLoading ? '保存中…' : '保存する' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <ConfirmModal
      v-model="showDeleteModal"
      title="カテゴリを削除"
      :message="`「${deleteTarget?.name}」を削除しますか？`"
      confirm-label="削除する"
      @confirm="executeDelete"
    />
  </div>
</template>
