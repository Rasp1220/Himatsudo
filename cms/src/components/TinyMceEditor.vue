<script setup lang="ts">
import { computed, onBeforeUnmount } from 'vue'
import Editor from '@tinymce/tinymce-vue'
import 'tinymce/tinymce'
import 'tinymce/themes/silver'
import 'tinymce/icons/default'
import 'tinymce/models/dom'
import 'tinymce/plugins/advlist'
import 'tinymce/plugins/autolink'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/link'
import 'tinymce/plugins/image'
import 'tinymce/plugins/charmap'
import 'tinymce/plugins/preview'
import 'tinymce/plugins/searchreplace'
import 'tinymce/plugins/visualblocks'
import 'tinymce/plugins/code'
import 'tinymce/plugins/fullscreen'
import 'tinymce/plugins/insertdatetime'
import 'tinymce/plugins/media'
import 'tinymce/plugins/table'
import 'tinymce/plugins/help'
import 'tinymce/plugins/wordcount'
import 'tinymce/plugins/codesample'

const props = withDefaults(defineProps<{
  modelValue: string
  height?: number
  disabled?: boolean
}>(), {
  height: 600,
  disabled: false,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const editorId = `tinymce-${Math.random().toString(36).slice(2, 9)}`

const localValue = computed({
  get: () => props.modelValue,
  set: (v: string) => emit('update:modelValue', v),
})

onBeforeUnmount(() => {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const tinymce = (window as any).tinymce
  if (tinymce) {
    const ed = tinymce.get(editorId)
    if (ed) ed.remove()
  }
})

const editorConfig = computed(() => ({
  base_url: '/tinymce',
  suffix: '.min',
  height: props.height,
  menubar: 'file edit view insert format tools table help',
  plugins: [
    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
    'searchreplace', 'visualblocks', 'code', 'fullscreen',
    'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample',
  ],
  toolbar:
    'undo redo | blocks | bold italic underline strikethrough | ' +
    'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
    'bullist numlist outdent indent | link image media table codesample | ' +
    'removeformat code fullscreen | help',
  content_style: `
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Hiragino Sans", "Noto Sans JP", sans-serif;
      font-size: 16px;
      line-height: 1.8;
      color: #1e293b;
      max-width: 100%;
      padding: 16px;
    }
    img { max-width: 100%; height: auto; }
    pre { background: #f1f5f9; border-radius: 4px; padding: 12px; overflow-x: auto; }
  `,
  image_advtab: true,
  link_assume_external_targets: true,
  relative_urls: false,
  remove_script_host: false,
  convert_urls: false,
  branding: false,
  promotion: false,
}))
</script>

<template>
  <Editor
    :id="editorId"
    v-model="localValue"
    license-key="gpl"
    :init="editorConfig"
    :disabled="disabled"
  />
</template>
