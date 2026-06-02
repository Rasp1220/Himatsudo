<script setup lang="ts">
import type { VideoBlock } from '@/types'

const props = defineProps<{ modelValue: VideoBlock }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: VideoBlock): void }>()

function extractVideoId(url: string): string {
  const m = url.match(/(?:youtube\.com\/watch\?(?:.*&)?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/)
  return m?.[1] ?? ''
}

function handleUrlInput(url: string) {
  const videoId = extractVideoId(url)
  emit('update:modelValue', { ...props.modelValue, youtube_url: url, video_id: videoId })
}

function update<K extends keyof VideoBlock>(key: K, value: VideoBlock[K]) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}
</script>

<template>
  <div class="space-y-3">
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">YouTube URL <span class="text-red-500">*</span></label>
      <input
        :value="modelValue.youtube_url"
        @input="handleUrlInput(($event.target as HTMLInputElement).value)"
        placeholder="https://www.youtube.com/watch?v=..."
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
      />
      <p v-if="modelValue.video_id" class="text-xs text-gray-400 mt-0.5">
        動画 ID: <span class="font-mono">{{ modelValue.video_id }}</span>
      </p>
    </div>
    <div v-if="modelValue.video_id" class="aspect-video rounded-md overflow-hidden border border-gray-200">
      <iframe
        :src="`https://www.youtube.com/embed/${modelValue.video_id}`"
        class="w-full h-full"
        allowfullscreen
        frameborder="0"
      />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">キャプション</label>
      <input
        :value="modelValue.caption"
        @input="update('caption', ($event.target as HTMLInputElement).value)"
        placeholder="動画の説明文（省略可）"
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
      />
    </div>
  </div>
</template>
