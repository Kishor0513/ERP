<script setup lang="ts">
import StatusBadge from '@/components/StatusBadge.vue'

export interface KanbanColumn {
  key: string
  label: string
  match?: string[]
}

defineProps<{
  columns: KanbanColumn[]
  items: any[]
  statusKey?: string
  titleKey?: string
  subtitleKey?: string
  badgeType?: 'order' | 'status' | 'payment' | 'shipment' | 'production'
}>()

const emit = defineEmits<{ (e: 'move', id: number, to: string): void; (e: 'open', id: number): void }>()

const itemsFor = (items: any[], statusKey: string, col: KanbanColumn) => {
  const keys = col.match && col.match.length ? col.match : [col.key]
  return items.filter((i) => keys.includes(String(i[statusKey || 'status'])))
}
</script>

<template>
  <div class="grid grid-flow-col auto-cols-[280px] gap-4 overflow-x-auto pb-4 scrollbar-thin">
    <div v-for="col in columns" :key="col.key" class="card !bg-slate-50 dark:!bg-slate-900/60">
      <div class="px-4 py-3 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
        <span class="text-[13px] font-semibold text-slate-700 dark:text-slate-200">{{ col.label }}</span>
        <span class="badge bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">{{ itemsFor(items, statusKey || 'status', col).length }}</span>
      </div>
      <div class="p-2.5 space-y-2.5 min-h-24">
        <button v-for="item in itemsFor(items, statusKey || 'status', col)" :key="item.id" @click="emit('open', item.id)" class="w-full text-left card card-pad !p-3.5 hover:shadow-pop hover:-translate-y-px transition-all">
          <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ item[titleKey || 'name'] || item.order_number || `#${item.id}` }}</p>
          <p v-if="subtitleKey && item[subtitleKey]" class="mt-0.5 text-xs text-slate-500 truncate">{{ item[subtitleKey] }}</p>
          <div class="mt-2 flex items-center justify-between gap-2">
            <StatusBadge :status="item[statusKey || 'status']" :type="badgeType || 'order'" />
            <span class="text-[11px] text-slate-400">{{ item.total ? '$' + Number(item.total).toLocaleString() : '' }}</span>
          </div>
        </button>
        <p v-if="!itemsFor(items, statusKey || 'status', col).length" class="text-xs text-slate-400 text-center py-4">Empty</p>
      </div>
    </div>
  </div>
</template>
