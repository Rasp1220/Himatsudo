<script setup lang="ts">
import { ref, watch } from 'vue'
import type { ArticleBlock } from '@/types'
import HeadingBlock from './HeadingBlock.vue'
import TextBlock from './TextBlock.vue'
import ImageBlock from './ImageBlock.vue'
import VideoBlock from './VideoBlock.vue'

const props = defineProps<{ modelValue: string }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: string): void }>()

const blockTypes = [
  { value: 'heading' as const, label: '見出し' },
  { value: 'text'    as const, label: '本文' },
  { value: 'image'   as const, label: '画像' },
  { value: 'video'   as const, label: '動画' },
]

function parseBlocks(json: string): ArticleBlock[] {
  if (!json) return []
  try { return JSON.parse(json) } catch { return [] }
}

const blocks = ref<ArticleBlock[]>(parseBlocks(props.modelValue))

watch(() => props.modelValue, (v) => {
  const parsed = parseBlocks(v)
  if (JSON.stringify(parsed) !== JSON.stringify(blocks.value)) {
    blocks.value = parsed
  }
})

function emit_blocks() {
  emit('update:modelValue', JSON.stringify(blocks.value))
}

function generateId(): string {
  return Math.random().toString(36).slice(2, 10)
}

function addBlock(type: ArticleBlock['type']) {
  const id = generateId()
  const newBlock: ArticleBlock = type === 'heading'
    ? { id, type: 'heading', level: 2, text: '' }
    : type === 'text'
    ? { id, type: 'text', html: '' }
    : type === 'image'
    ? { id, type: 'image', url: '', alt: '', caption: '' }
    : { id, type: 'video', youtube_url: '', video_id: '', caption: '' }
  blocks.value = [...blocks.value, newBlock]
  emit_blocks()
}

function updateBlock(idx: number, updated: ArticleBlock) {
  const next = [...blocks.value]
  next[idx] = updated
  blocks.value = next
  emit_blocks()
}

function removeBlock(idx: number) {
  blocks.value = blocks.value.filter((_, i) => i !== idx)
  emit_blocks()
}

function moveUp(idx: number) {
  if (idx === 0) return
  const next = [...blocks.value]
  ;[next[idx - 1], next[idx]] = [next[idx], next[idx - 1]]
  blocks.value = next
  emit_blocks()
}

function moveDown(idx: number) {
  if (idx === blocks.value.length - 1) return
  const next = [...blocks.value]
  ;[next[idx], next[idx + 1]] = [next[idx + 1], next[idx]]
  blocks.value = next
  emit_blocks()
}

function blockLabel(type: string): string {
  const map: Record<string, string> = {
    heading: '見出し',
    text: '本文',
    image: '画像',
    video: '動画',
  }
  return map[type] ?? type
}

function badgeClass(type: string): string {
  const map: Record<string, string> = {
    heading: 'bg-purple-100 text-purple-700',
    text:    'bg-blue-100 text-blue-700',
    image:   'bg-green-100 text-green-700',
    video:   'bg-red-100 text-red-700',
  }
  return map[type] ?? 'bg-gray-100 text-gray-600'
}
</script>

<template>
  <div class="space-y-3">
    <!-- Block list -->
    <div v-for="(block, idx) in blocks" :key="block.id" class="block-item bg-white border border-gray-200 rounded-lg overflow-hidden">
      <!-- Block header -->
      <div class="flex items-center justify-between px-3 py-2 bg-gray-50 border-b border-gray-200">
        <span class="flex items-center gap-2">
          <span class="text-xs font-semibold px-2 py-0.5 rounded" :class="badgeClass(block.type)">
            {{ blockLabel(block.type) }}
          </span>
        </span>
        <div class="flex items-center gap-1">
          <button
            type="button"
            @click="moveUp(idx)"
            :disabled="idx === 0"
            title="上へ移動"
            class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30"
          >▲</button>
          <button
            type="button"
            @click="moveDown(idx)"
            :disabled="idx === blocks.length - 1"
            title="下へ移動"
            class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30"
          >▼</button>
          <button
            type="button"
            @click="removeBlock(idx)"
            title="削除"
            class="p-1 text-red-400 hover:text-red-600 ml-1"
          >✕</button>
        </div>
      </div>
      <!-- Block body -->
      <div class="p-3">
        <HeadingBlock
          v-if="block.type === 'heading'"
          :modelValue="block"
          @update:modelValue="updateBlock(idx, $event)"
        />
        <TextBlock
          v-else-if="block.type === 'text'"
          :modelValue="block"
          @update:modelValue="updateBlock(idx, $event)"
        />
        <ImageBlock
          v-else-if="block.type === 'image'"
          :modelValue="block"
          @update:modelValue="updateBlock(idx, $event)"
        />
        <VideoBlock
          v-else-if="block.type === 'video'"
          :modelValue="block"
          @update:modelValue="updateBlock(idx, $event)"
        />
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="blocks.length === 0" class="text-center py-8 text-gray-400 text-sm border-2 border-dashed border-gray-200 rounded-lg">
      下のボタンからブロックを追加してください
    </div>

    <!-- Add block buttons -->
    <div class="flex flex-wrap gap-2 pt-1">
      <button
        v-for="type in blockTypes"
        :key="type.value"
        type="button"
        @click="addBlock(type.value)"
        class="flex items-center gap-1 px-3 py-1.5 text-sm border border-gray-300 rounded-md hover:bg-gray-50 hover:border-gray-400 transition-colors"
      >
        <span>＋</span>
        <span>{{ type.label }}</span>
      </button>
    </div>
  </div>
</template>
