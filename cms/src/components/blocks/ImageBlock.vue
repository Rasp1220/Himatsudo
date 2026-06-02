<template>
  <div class="space-y-3">
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">画像 URL <span class="text-red-500">*</span></label>
      <input
        :value="modelValue.url"
        @input="update('url', ($event.target as HTMLInputElement).value)"
        type="url"
        placeholder="https://example.com/image.jpg"
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
      />
    </div>
    <div v-if="modelValue.url" class="rounded-md overflow-hidden border border-gray-200 bg-gray-50 max-h-64 flex items-center justify-center">
      <img
        :src="modelValue.url"
        :alt="modelValue.alt || ''"
        class="max-h-64 max-w-full object-contain"
        @error="imageError = true"
        @load="imageError = false"
      />
      <p v-if="imageError" class="text-xs text-gray-400 p-4">画像を読み込めません</p>
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

<script setup lang="ts">
import { ref } from 'vue'
import type { ImageBlock } from '@/types'

const props = defineProps<{ modelValue: ImageBlock }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: ImageBlock): void }>()

const imageError = ref(false)

function update<K extends keyof ImageBlock>(key: K, value: ImageBlock[K]) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}
</script>
