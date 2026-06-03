<script setup lang="ts">
import { ref } from 'vue'
import { uploadApi } from '@/api/client'
import type { ImageBlock } from '@/types'

const props = defineProps<{ modelValue: ImageBlock }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: ImageBlock): void }>()

const uploading = ref(false)
const uploadError = ref('')
const fileInput = ref<HTMLInputElement | null>(null)

function update<K extends keyof ImageBlock>(key: K, value: ImageBlock[K]) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}

async function handleFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  uploading.value = true
  uploadError.value = ''
  try {
    const result = await uploadApi.upload(file)
    emit('update:modelValue', { ...props.modelValue, url: result.url })
  } catch {
    uploadError.value = 'アップロードに失敗しました'
  } finally {
    uploading.value = false
    input.value = ''
  }
}

function removeImage() {
  emit('update:modelValue', { ...props.modelValue, url: '' })
}
</script>

<template>
  <div class="space-y-3">
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">画像 <span class="text-red-500">*</span></label>
      <input
        ref="fileInput"
        type="file"
        accept="image/jpeg,image/png,image/gif,image/webp"
        class="hidden"
        @change="handleFileChange"
      />
      <div v-if="modelValue.url" class="relative inline-block mb-2">
        <img
          :src="modelValue.url"
          :alt="modelValue.alt || ''"
          class="max-h-48 max-w-full rounded border border-gray-200 object-contain"
        />
        <button
          type="button"
          @click="removeImage"
          class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs leading-none hover:bg-red-600"
        >✕</button>
      </div>
      <div v-else class="mb-2 w-40 h-24 bg-gray-100 border border-dashed border-gray-300 rounded flex items-center justify-center text-xs text-gray-400">
        NO IMAGE
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          :disabled="uploading"
          @click="fileInput?.click()"
          class="px-3 py-1.5 text-xs bg-gray-100 border border-gray-300 rounded hover:bg-gray-200 disabled:opacity-50"
        >
          {{ uploading ? 'アップロード中…' : '画像を選択' }}
        </button>
        <span v-if="uploadError" class="text-xs text-red-500">{{ uploadError }}</span>
      </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">alt テキスト</label>
        <input
          :value="modelValue.alt"
          @input="update('alt', ($event.target as HTMLInputElement).value)"
          placeholder="画像の説明"
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">キャプション</label>
        <input
          :value="modelValue.caption"
          @input="update('caption', ($event.target as HTMLInputElement).value)"
          placeholder="画像の説明文（省略可）"
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>
    </div>
  </div>
</template>
