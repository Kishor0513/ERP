<script setup lang="ts">
defineProps<{ title?: string; hint?: string }>()
</script>

<template>
  <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
      <slot name="icon">
        <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2h6" />
        </svg>
      </slot>
    </div>
    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ title || 'Nothing here yet' }}</p>
    <p v-if="hint" class="mt-1 text-sm text-slate-500 dark:text-slate-400 max-w-sm">{{ hint }}</p>
    <div class="mt-4"><slot name="actions" /></div>
  </div>
</template>
