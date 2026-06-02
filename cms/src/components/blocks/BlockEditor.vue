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

function makeBlock(type: ArticleBlock['type']): ArticleBlock {
  const id = generateId()
  if (type === 'heading') return { id, type: 'heading', level: 2, text: '' }
  if (type === 'text')    return { id, type: 'text', html: '' }
  if (type === 'image')   return { id, type: 'image', url: '', alt: '', caption: '' }
  return { id, type: 'video', youtube_url: '', video_id: '', caption: '' }
}

// 末尾に追加
function addBlock(type: ArticleBlock['type']) {
  blocks.value = [...blocks.value, makeBlock(type)]
  emit_blocks()
}

// afterIdx の直後に挿入 (-1 = 先頭に挿入)
function insertBlock(type: ArticleBlock['type'], afterIdx: number) {
  const next = [...blocks.value]
  next.splice(afterIdx + 1, 0, makeBlock(type))
  blocks.value = next
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
    heading: '見出し', text: '本文', image: '画像', video: '動画',
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
  <div class="space-y-0">

    <!-- 先頭への挿入ゾーン（ブロックが1つ以上ある場合） -->
    <div
      v-if="blocks.length > 0"
      class="group flex items-center gap-2 py-1.5 mb-1"
    >
      <div class="flex-1 h-px bg-gray-100 group-hover:bg-gray-300 transition-colors"></div>
      <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <button
          v-for="type in blockTypes"
          :key="type.value"
          type="button"
          @click="insertBlock(type.value, -1)"
          class="px-2 py-0.5 text-xs border border-gray-300 rounded bg-white hover:bg-gray-50 text-gray-500 hover:text-gray-700"
        >＋{{ type.label }}</button>
      </div>
      <div class="flex-1 h-px bg-gray-100 group-hover:bg-gray-300 transition-colors"></div>
    </div>

    <!-- ブロックリスト -->
    <template v-for="(block, idx) in blocks" :key="block.id">
      <div class="block-item bg-white border border-gray-200 rounded-lg overflow-hidden">
        <!-- ブロックヘッダー -->
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
        <!-- ブロック本体 -->
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

      <!-- ブロック間の挿入ゾーン -->
      <div class="group flex items-center gap-2 py-1.5">
        <div class="flex-1 h-px bg-gray-100 group-hover:bg-gray-300 transition-colors"></div>
        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
          <button
            v-for="type in blockTypes"
            :key="type.value"
            type="button"
            @click="insertBlock(type.value, idx)"
            class="px-2 py-0.5 text-xs border border-gray-300 rounded bg-white hover:bg-gray-50 text-gray-500 hover:text-gray-700"
          >＋{{ type.label }}</button>
        </div>
        <div class="flex-1 h-px bg-gray-100 group-hover:bg-gray-300 transition-colors"></div>
      </div>
    </template>

    <!-- 空の状態 -->
    <div
      v-if="blocks.length === 0"
      class="text-center py-8 text-gray-400 text-sm border-2 border-dashed border-gray-200 rounded-lg"
    >
      下のボタンからブロックを追加してください
    </div>

    <!-- 末尾への追加ボタン -->
    <div class="flex flex-wrap gap-2 pt-2">
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
