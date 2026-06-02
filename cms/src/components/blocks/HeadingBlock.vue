<template>
  <div class="space-y-2">
    <div class="flex items-center gap-2">
      <select
        :value="modelValue.level"
        @change="update('level', Number(($event.target as HTMLSelectElement).value) as 2 | 3 | 4)"
        class="px-2 py-1 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-400"
      >
        <option :value="2">H2 大見出し</option>
        <option :value="3">H3 中見出し</option>
        <option :value="4">H4 小見出し</option>
      </select>
    </div>
    <input
      :value="modelValue.text"
      @input="update('text', ($event.target as HTMLInputElement).value)"
      :placeholder="levelPlaceholder"
      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
      :class="{
        'text-2xl font-bold': modelValue.level === 2,
        'text-xl font-semibold': modelValue.level === 3,
        'text-lg font-medium': modelValue.level === 4,
      }"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { HeadingBlock } from '@/types'

const props = defineProps<{ modelValue: HeadingBlock }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: HeadingBlock): void }>()

const levelPlaceholder = computed(() => {
  const map: Record<number, string> = { 2: '大見出しを入力…', 3: '中見出しを入力…', 4: '小見出しを入力…' }
  return map[props.modelValue.level] ?? '見出しを入力…'
})

function update<K extends keyof HeadingBlock>(key: K, value: HeadingBlock[K]) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}
</script>
