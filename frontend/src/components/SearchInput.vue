<script setup lang="ts">
import { ref, watch } from 'vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

interface Props {
  modelValue?: string
  placeholder?: string
  debounce?: number
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Search...',
  debounce: 300,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'search', value: string): void
}>()

const searchQuery = ref(props.modelValue)
let timeout: ReturnType<typeof setTimeout>

const onInput = (e: Event) => {
  const value = (e.target as HTMLInputElement).value
  searchQuery.value = value
  clearTimeout(timeout)
  timeout = setTimeout(() => {
    emit('update:modelValue', value)
    emit('search', value)
  }, props.debounce)
}

watch(
  () => props.modelValue,
  (newValue) => {
    searchQuery.value = newValue
  }
)
</script>

<template>
  <div class="relative">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
      <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
    </div>
    <input
      type="text"
      :value="searchQuery"
      @input="onInput"
      :placeholder="placeholder"
      class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white pl-10 pr-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500"
    />
  </div>
</template>
