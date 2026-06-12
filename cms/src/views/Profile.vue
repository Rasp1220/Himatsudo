<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { uploadApi } from '@/api/client'

const auth = useAuthStore()

const loading = ref(true)
const saving = ref(false)
const errorMsg = ref('')
const successMsg = ref('')

const avatarUploading = ref(false)
const avatarError = ref('')

const form = reactive({
  name: '',
  email: '',
  avatar: '',
  bio: '',
  password: '',
})

onMounted(async () => {
  try {
    const me = await auth.fetchProfile()
    form.name = me.name ?? ''
    form.email = me.email ?? ''
    form.avatar = me.avatar ?? ''
    form.bio = me.bio ?? ''
  } catch {
    errorMsg.value = 'プロフィールの読み込みに失敗しました。'
  } finally {
    loading.value = false
  }
})

async function handleAvatarChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  avatarUploading.value = true
  avatarError.value = ''
  try {
    const result = await uploadApi.upload(file)
    form.avatar = result.url
  } catch {
    avatarError.value = '画像のアップロードに失敗しました'
  } finally {
    avatarUploading.value = false
    input.value = ''
  }
}

function removeAvatar() {
  form.avatar = ''
}

async function handleSubmit() {
  errorMsg.value = ''
  successMsg.value = ''
  if (!form.name.trim() || !form.email.trim()) {
    errorMsg.value = '名前とメールアドレスは必須です。'
    return
  }

  saving.value = true
  try {
    await auth.updateProfile({
      name: form.name,
      email: form.email,
      avatar: form.avatar,
      bio: form.bio,
      ...(form.password ? { password: form.password } : {}),
    })
    form.password = ''
    successMsg.value = 'プロフィールを更新しました。'
  } catch {
    errorMsg.value = '保存に失敗しました。メールアドレスが既に使われていないか確認してください。'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="max-w-xl">
    <h2 class="text-xl font-bold text-gray-800 mb-6">プロフィール設定</h2>

    <div v-if="loading" class="text-sm text-gray-500">読み込み中…</div>

    <form
      v-else
      @submit.prevent="handleSubmit"
      class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 space-y-5"
    >
      <!-- Avatar -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">アイコン画像</label>
        <div class="flex items-center gap-4">
          <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center flex-shrink-0">
            <img v-if="form.avatar" :src="form.avatar" alt="アイコン" class="w-full h-full object-cover" />
            <span v-else class="text-2xl font-bold text-gray-400">
              {{ form.name ? form.name.charAt(0) : '?' }}
            </span>
          </div>
          <div class="space-y-2">
            <label
              class="inline-block px-3 py-2 text-sm border border-gray-300 rounded-md cursor-pointer hover:bg-gray-50"
            >
              {{ avatarUploading ? 'アップロード中…' : '画像を選択' }}
              <input type="file" accept="image/*" class="hidden" @change="handleAvatarChange" :disabled="avatarUploading" />
            </label>
            <button
              v-if="form.avatar"
              type="button"
              @click="removeAvatar"
              class="block text-xs text-red-500 hover:underline"
            >
              削除する
            </button>
            <p v-if="avatarError" class="text-xs text-red-600">{{ avatarError }}</p>
          </div>
        </div>
      </div>

      <!-- Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">名前 <span class="text-red-500">*</span></label>
        <input
          v-model="form.name"
          required
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">メールアドレス <span class="text-red-500">*</span></label>
        <input
          v-model="form.email"
          type="email"
          required
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <!-- Bio -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">自己紹介</label>
        <textarea
          v-model="form.bio"
          rows="4"
          placeholder="プロフィールに表示される自己紹介文です"
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
        ></textarea>
      </div>

      <!-- Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          パスワード <span class="text-xs text-gray-400">（変更する場合のみ入力）</span>
        </label>
        <input
          v-model="form.password"
          type="password"
          autocomplete="new-password"
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <p v-if="errorMsg" class="text-sm text-red-600">{{ errorMsg }}</p>
      <p v-if="successMsg" class="text-sm text-green-600">{{ successMsg }}</p>

      <div class="flex justify-end pt-2">
        <button
          type="submit"
          :disabled="saving"
          class="px-5 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          {{ saving ? '保存中…' : '保存する' }}
        </button>
      </div>
    </form>
  </div>
</template>
