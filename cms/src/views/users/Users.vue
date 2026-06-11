<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { useUsersStore } from '@/stores/users'
import type { User } from '@/types'
import DataTable from '@/components/ui/DataTable.vue'
import Pagination from '@/components/ui/Pagination.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'

const usersStore = useUsersStore()

const columns = [
  { key: 'name', label: '名前' },
  { key: 'email', label: 'メールアドレス' },
  { key: 'role', label: 'ロール', width: '100px' },
  { key: 'created_at', label: '登録日', width: '120px' },
  { key: 'actions', label: '', width: '100px' },
]

const showModal = ref(false)
const editTarget = ref<User | null>(null)
const modalLoading = ref(false)
const modalError = ref('')
const modalForm = reactive({ name: '', email: '', password: '', role: 'editor' as User['role'] })

const showDeleteModal = ref(false)
const deleteTarget = ref<User | null>(null)

function formatDate(d: string): string {
  return new Date(d).toLocaleDateString('ja-JP')
}

function openCreate() {
  editTarget.value = null
  Object.assign(modalForm, { name: '', email: '', password: '', role: 'editor' })
  showModal.value = true
}

function openEdit(u: User) {
  editTarget.value = u
  Object.assign(modalForm, { name: u.name, email: u.email, password: '', role: u.role })
  showModal.value = true
}

async function handleSave() {
  modalLoading.value = true
  modalError.value = ''
  try {
    if (editTarget.value) {
      const payload: Partial<User> & { password?: string } = { name: modalForm.name, email: modalForm.email, role: modalForm.role }
      if (modalForm.password) payload.password = modalForm.password
      await usersStore.update(editTarget.value.id, payload)
    } else {
      await usersStore.create({ name: modalForm.name, email: modalForm.email, password: modalForm.password, role: modalForm.role })
    }
    showModal.value = false
  } catch {
    modalError.value = '保存に失敗しました。メールアドレスが既に使われていないか確認してください。'
  } finally {
    modalLoading.value = false
  }
}

function confirmDelete(u: User) {
  deleteTarget.value = u
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!deleteTarget.value) return
  await usersStore.remove(deleteTarget.value.id)
  deleteTarget.value = null
}

async function fetchPage(page: number) {
  await usersStore.fetchList(page, 20)
}

onMounted(() => usersStore.fetchList())
</script>

<template>
  <div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-800">ユーザー管理</h2>
      <button
        @click="openCreate"
        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700"
      >
        ＋ ユーザーを追加
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
      <DataTable :columns="columns" :rows="usersStore.items" :loading="usersStore.loading">
        <template #role="{ value }">
          <span
            class="px-2 py-0.5 text-xs font-medium rounded"
            :class="value === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600'"
          >
            {{ value === 'admin' ? '管理者' : '編集者' }}
          </span>
        </template>
        <template #created_at="{ value }">
          <span class="text-xs text-gray-500">{{ formatDate(value as string) }}</span>
        </template>
        <template #actions="{ row }">
          <div class="flex gap-2">
            <button @click="openEdit(row as User)" class="text-blue-600 hover:underline text-xs font-medium">編集</button>
            <button @click="confirmDelete(row as User)" class="text-red-500 hover:underline text-xs font-medium">削除</button>
          </div>
        </template>
      </DataTable>

      <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">全 {{ usersStore.pagination.total }} 件</p>
        <Pagination
          :current-page="usersStore.pagination.page"
          :last-page="usersStore.pagination.last_page"
          @change="fetchPage"
        />
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="showModal = false"
      >
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
          <h3 class="text-lg font-semibold mb-4">{{ editTarget ? 'ユーザーを編集' : 'ユーザーを追加' }}</h3>
          <form @submit.prevent="handleSave" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">名前 <span class="text-red-500">*</span></label>
              <input v-model="modalForm.name" required class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">メールアドレス <span class="text-red-500">*</span></label>
              <input v-model="modalForm.email" type="email" required class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                パスワード {{ editTarget ? '（変更する場合のみ入力）' : '' }}
                <span v-if="!editTarget" class="text-red-500">*</span>
              </label>
              <input
                v-model="modalForm.password"
                type="password"
                :required="!editTarget"
                autocomplete="new-password"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">ロール</label>
              <select v-model="modalForm.role" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                <option value="editor">編集者</option>
                <option value="admin">管理者</option>
              </select>
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
      title="ユーザーを削除"
      :message="`「${deleteTarget?.name}」を削除しますか？`"
      confirm-label="削除する"
      @confirm="executeDelete"
    />
  </div>
</template>
