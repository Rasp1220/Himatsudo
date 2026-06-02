<script setup lang="ts">
interface Column {
  key: string
  label: string
  width?: string
}

defineProps<{
  columns: Column[]
  rows: unknown[]
  loading?: boolean
}>()
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
            :style="col.width ? `width:${col.width}` : ''"
          >
            {{ col.label }}
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
        <tr v-if="loading">
          <td :colspan="columns.length" class="px-4 py-8 text-center text-sm text-gray-400">
            読み込み中…
          </td>
        </tr>
        <tr v-else-if="!rows.length">
          <td :colspan="columns.length" class="px-4 py-8 text-center text-sm text-gray-400">
            データがありません
          </td>
        </tr>
        <tr
          v-else
          v-for="(row, idx) in rows"
          :key="idx"
          class="hover:bg-gray-50 transition-colors"
        >
          <td
            v-for="col in columns"
            :key="col.key"
            class="px-4 py-3 text-sm text-gray-700"
          >
            <slot :name="col.key" :row="row" :value="(row as Record<string, unknown>)[col.key]">
              {{ (row as Record<string, unknown>)[col.key] }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
