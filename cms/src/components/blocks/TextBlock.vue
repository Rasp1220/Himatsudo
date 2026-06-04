<script setup lang="ts">
import TinyMceEditor from '@/components/TinyMceEditor.vue'
import type { TextBlock } from '@/types'

const props = defineProps<{ modelValue: TextBlock }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: TextBlock): void }>()

function onUpdate(html: string) {
  emit('update:modelValue', { ...props.modelValue, html })
}
</script>

<template>
  <TinyMceEditor
    :modelValue="modelValue.html"
    @update:modelValue="onUpdate"
    :height="300"
  />
</template>
