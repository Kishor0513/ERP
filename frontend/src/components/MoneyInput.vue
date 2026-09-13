<script setup lang="ts">
import { ref, watch } from 'vue'

interface Props {
  modelValue: number | string
  currency?: string
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  currency: 'USD',
  disabled: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: number): void
}>()

const displayValue = ref(formatCurrency(Number(props.modelValue)))

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: props.currency,
  }).format(value)
}

function parseValue(input: string): number {
  const cleaned = input.replace(/[^0-9.-]/g, '')
  return parseFloat(cleaned) || 0
}

const onInput = (e: Event) => {
  const input = e.target as HTMLInputElement
  const value = parseValue(input.value)
  displayValue.value = formatCurrency(value)
  emit('update:modelValue', value)
}

watch(
  () => props.modelValue,
  (newValue) => {
    displayValue.value = formatCurrency(Number(newValue))
  }
)
</script>

<template>
  <div class="relative rounded-md shadow-sm">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
      <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
    </div>
    <input
      type="text"
      :value="displayValue"
      @input="onInput"
      :disabled="disabled"
      class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white pl-7 pr-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:dark:bg-gray-800"
    />
  </div>
</template>
