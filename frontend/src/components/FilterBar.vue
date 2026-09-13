<script setup lang="ts">
export interface FilterDef {
  key: string
  label: string
  type: 'select' | 'text'
  options?: { value: string; label: string }[]
  placeholder?: string
}

defineProps<{ filters: FilterDef[]; values: Record<string, any> }>()
const emit = defineEmits<{ (e: 'change', key: string, value: any): void; (e: 'reset'): void }>()
</script>

<template>
  <div class="card card-pad !py-4 flex flex-wrap items-end gap-3">
    <div v-for="f in filters" :key="f.key" class="min-w-40 flex-1 sm:flex-none">
      <label class="label !mb-1">{{ f.label }}</label>
      <select v-if="f.type === 'select'" :value="values[f.key] || ''" @change="emit('change', f.key, ($event.target as HTMLSelectElement).value)" class="input !py-2">
        <option value="">All</option>
        <option v-for="o in f.options" :key="o.value" :value="o.value">{{ o.label }}</option>
      </select>
      <input v-else :value="values[f.key] || ''" :placeholder="f.placeholder || ''" @input="emit('change', f.key, ($event.target as HTMLInputElement).value)" class="input !py-2" />
    </div>
    <button @click="emit('reset')" class="btn-ghost !py-2">Reset</button>
    <div class="flex-1" />
    <slot name="actions" />
  </div>
</template>
