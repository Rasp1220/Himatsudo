<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import type { TextBlock } from '@/types'

const props = defineProps<{ modelValue: TextBlock }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: TextBlock): void }>()

const editor = useEditor({
  extensions: [StarterKit, Link.configure({ openOnClick: false })],
  content: props.modelValue.html || '',
  onUpdate({ editor: e }) {
    emit('update:modelValue', { ...props.modelValue, html: e.getHTML() })
  },
})

watch(
  () => props.modelValue.html,
  (newHtml) => {
    if (editor.value && editor.value.getHTML() !== newHtml) {
      editor.value.commands.setContent(newHtml || '', { emitUpdate: false })
    }
  },
)

const editorButtons = [
  { icon: 'B',   label: 'Bold',         action: () => editor.value?.chain().focus().toggleBold().run(),              active: () => editor.value?.isActive('bold') ?? false },
  { icon: 'I',   label: 'Italic',       action: () => editor.value?.chain().focus().toggleItalic().run(),            active: () => editor.value?.isActive('italic') ?? false },
  { icon: '•',   label: 'Bullet list',  action: () => editor.value?.chain().focus().toggleBulletList().run(),        active: () => editor.value?.isActive('bulletList') ?? false },
  { icon: '1.',  label: 'Ordered list', action: () => editor.value?.chain().focus().toggleOrderedList().run(),       active: () => editor.value?.isActive('orderedList') ?? false },
  { icon: '❝',   label: 'Quote',        action: () => editor.value?.chain().focus().toggleBlockquote().run(),        active: () => editor.value?.isActive('blockquote') ?? false },
  { icon: '</>',  label: 'Code',        action: () => editor.value?.chain().focus().toggleCode().run(),              active: () => editor.value?.isActive('code') ?? false },
  { icon: '—',   label: 'HR',           action: () => editor.value?.chain().focus().setHorizontalRule().run() },
]

onBeforeUnmount(() => editor.value?.destroy())
</script>

<template>
  <div class="border border-gray-300 rounded-md overflow-hidden">
    <div class="flex flex-wrap gap-1 p-2 bg-gray-50 border-b border-gray-200">
      <button
        v-for="btn in editorButtons"
        :key="btn.label"
        type="button"
        @click="btn.action()"
        :title="btn.label"
        class="px-2 py-1 text-xs font-medium rounded hover:bg-gray-200 transition-colors"
        :class="btn.active?.() ? 'bg-gray-200' : ''"
      >{{ btn.icon }}</button>
    </div>
    <EditorContent
      v-if="editor"
      :editor="editor"
      class="min-h-32 p-3 text-sm focus:outline-none prose prose-sm max-w-none"
    />
  </div>
</template>
