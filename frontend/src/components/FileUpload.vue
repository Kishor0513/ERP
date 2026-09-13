<script setup lang="ts">
import { ref } from 'vue'
import { PaperClipIcon, XMarkIcon } from '@heroicons/vue/24/outline'

interface Props {
  accept?: string
  multiple?: boolean
  maxSize?: number
}

const props = withDefaults(defineProps<Props>(), {
  accept: '*',
  multiple: false,
  maxSize: 10 * 1024 * 1024,
})

const emit = defineEmits<{
  (e: 'files', files: File[]): void
}>()

const isDragging = ref(false)
const files = ref<File[]>([])
const error = ref<string | null>(null)

const onDragEnter = (e: DragEvent) => {
  e.preventDefault()
  isDragging.value = true
}

const onDragLeave = (e: DragEvent) => {
  e.preventDefault()
  isDragging.value = false
}

const onDrop = (e: DragEvent) => {
  e.preventDefault()
  isDragging.value = false
  const droppedFiles = Array.from(e.dataTransfer?.files || [])
  handleFiles(droppedFiles)
}

const onFileSelect = (e: Event) => {
  const input = e.target as HTMLInputElement
  const selectedFiles = Array.from(input.files || [])
  handleFiles(selectedFiles)
}

const handleFiles = (newFiles: File[]) => {
  error.value = null
  for (const file of newFiles) {
    if (file.size > props.maxSize) {
      error.value = `File ${file.name} exceeds maximum size of ${Math.round(props.maxSize / 1024 / 1024)}MB`
      return
    }
  }
  if (props.multiple) {
    files.value = [...files.value, ...newFiles]
  } else {
    files.value = newFiles.slice(0, 1)
  }
  emit('files', files.value)
}

const removeFile = (index: number) => {
  files.value.splice(index, 1)
  emit('files', files.value)
}

const formatSize = (bytes: number) => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}
</script>

<template>
  <div>
    <div
      @dragenter="onDragEnter"
      @dragover.prevent
      @dragleave="onDragLeave"
      @drop="onDrop"
      :class="[
        isDragging
          ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
          : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500',
        'border-2 border-dashed rounded-lg p-6 text-center transition-colors',
      ]"
    >
      <PaperClipIcon class="mx-auto h-12 w-12 text-gray-400" />
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        <span class="font-medium text-primary-600 hover:text-primary-500">Click to upload</span>
        or drag and drop
      </p>
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
        {{ accept === '*' ? 'Any file' : accept }} up to {{ formatSize(maxSize) }}
      </p>
      <input
        type="file"
        :accept="accept"
        :multiple="multiple"
        class="hidden"
        @change="onFileSelect"
      />
    </div>

    <p v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ error }}</p>

    <ul v-if="files.length > 0" class="mt-4 space-y-2">
      <li
        v-for="(file, index) in files"
        :key="index"
        class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
      >
        <div class="flex items-center gap-2 min-w-0">
          <PaperClipIcon class="h-5 w-5 text-gray-400 flex-shrink-0" />
          <span class="text-sm text-gray-900 dark:text-white truncate">{{ file.name }}</span>
          <span class="text-xs text-gray-500 dark:text-gray-400">({{ formatSize(file.size) }})</span>
        </div>
        <button
          @click="removeFile(index)"
          class="ml-2 text-gray-400 hover:text-red-500"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </li>
    </ul>
  </div>
</template>
