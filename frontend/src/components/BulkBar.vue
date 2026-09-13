<script setup lang="ts">
defineProps<{ count: number }>()
const emit = defineEmits<{ (e: 'export'): void; (e: 'delete'): void; (e: 'clear'): void }>()
</script>

<template>
  <div v-if="count > 0" class="card card-pad !py-3 flex items-center gap-3 bg-primary-50/60 dark:bg-primary-950/30 border-primary-200 dark:border-primary-900">
    <span class="text-sm font-semibold text-primary-800 dark:text-primary-200">{{ count }} selected</span>
    <div class="flex gap-2">
      <button @click="emit('export')" class="btn-secondary !py-1.5 !text-[13px]">Export CSV</button>
      <button @click="emit('delete')" class="btn-danger !py-1.5 !text-[13px]">Delete</button>
      <button @click="emit('clear')" class="btn-ghost !py-1.5 !text-[13px]">Clear</button>
    </div>
  </div>
</template>
