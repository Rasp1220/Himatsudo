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
  instagram_url: '',
  twitter_url: '',
  tiktok_url: '',
  password: '',
})

onMounted(async () => {
  try {
    const me = await auth.fetchProfile()
    form.name = me.name ?? ''
    form.email = me.email ?? ''
    form.avatar = me.avatar ?? ''
    form.bio = me.bio ?? ''
    form.instagram_url = me.instagram_url ?? ''
    form.twitter_url = me.twitter_url ?? ''
    form.tiktok_url = me.tiktok_url ?? ''
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
      ...(form.instagram_url ? { instagram_url: form.instagram_url } : {}),
      ...(form.twitter_url ? { twitter_url: form.twitter_url } : {}),
      ...(form.tiktok_url ? { tiktok_url: form.tiktok_url } : {}),
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

      <!-- SNS Links -->
      <div class="space-y-3">
        <p class="text-sm font-medium text-gray-700">SNSリンク <span class="text-xs text-gray-400">（設定したものだけ公開されます）</span></p>
        <div class="flex items-center gap-3">
          <span class="w-6 flex-shrink-0" title="Instagram">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current text-pink-500"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
          </span>
          <input
            v-model="form.instagram_url"
            type="url"
            placeholder="https://www.instagram.com/yourname"
            class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>
        <div class="flex items-center gap-3">
          <span class="w-6 flex-shrink-0" title="X (Twitter)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current text-gray-800"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.742l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          </span>
          <input
            v-model="form.twitter_url"
            type="url"
            placeholder="https://x.com/yourname"
            class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>
        <div class="flex items-center gap-3">
          <span class="w-6 flex-shrink-0" title="TikTok">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5 fill-current text-gray-900"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.79a8.18 8.18 0 004.78 1.52V6.85a4.85 4.85 0 01-1.01-.16z"/></svg>
          </span>
          <input
            v-model="form.tiktok_url"
            type="url"
            placeholder="https://www.tiktok.com/@yourname"
            class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>
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
