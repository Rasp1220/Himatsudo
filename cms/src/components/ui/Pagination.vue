<template>
  <div v-if="lastPage > 1" class="flex items-center gap-1 justify-center mt-6">
    <button
      @click="$emit('change', currentPage - 1)"
      :disabled="currentPage <= 1"
      class="px-3 py-1.5 text-sm border border-gray-300 rounded disabled:opacity-40 hover:bg-gray-50"
    >
      &laquo;
    </button>
    <template v-for="p in pages" :key="p">
      <span v-if="p === '...'" class="px-2 py-1.5 text-sm text-gray-400">…</span>
      <button
        v-else
        @click="$emit('change', p as number)"
        :class="[
          'px-3 py-1.5 text-sm border rounded',
          p === currentPage
            ? 'bg-blue-600 text-white border-blue-600'
            : 'border-gray-300 hover:bg-gray-50',
        ]"
      >
        {{ p }}
      </button>
    </template>
    <button
      @click="$emit('change', currentPage + 1)"
      :disabled="currentPage >= lastPage"
      class="px-3 py-1.5 text-sm border border-gray-300 rounded disabled:opacity-40 hover:bg-gray-50"
    >
      &raquo;
    </button>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ currentPage: number; lastPage: number }>()
defineEmits<{ change: [page: number] }>()

const pages = computed(() => {
  const result: (number | string)[] = []
  const range = 2
  for (let i = 1; i <= props.lastPage; i++) {
    if (
      i === 1 ||
      i === props.lastPage ||
      (i >= props.currentPage - range && i <= props.currentPage + range)
    ) {
      result.push(i)
    } else if (result[result.length - 1] !== '...') {
      result.push('...')
    }
  }
  return result
})
</script>
