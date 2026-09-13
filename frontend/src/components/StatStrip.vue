<script setup lang="ts">
export interface Stat {
  label: string
  value: string | number
  hint?: string
}
defineProps<{ stats: Stat[]; loading?: boolean }>()
</script>

<template>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div v-for="s in stats" :key="s.label" class="card card-pad">
      <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ s.label }}</p>
      <p v-if="loading" class="skeleton h-7 w-24 mt-2" />
      <p v-else class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ s.value }}</p>
      <p v-if="s.hint" class="mt-1 text-xs text-slate-400">{{ s.hint }}</p>
    </div>
  </div>
</template>
