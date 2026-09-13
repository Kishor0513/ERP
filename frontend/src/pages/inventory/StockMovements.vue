<script setup lang="ts">
import { onMounted } from 'vue'
import { useInventoryStore } from '@/stores/inventory'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import DataTable from '@/components/DataTable.vue'
import BulkBar from '@/components/BulkBar.vue'
import { useList } from '@/composables/useList'

const inventoryStore = useInventoryStore()

const columns = [
  { key: 'created_at', label: 'Date', format: (v: string) => new Date(v).toLocaleDateString() },
  { key: 'type', label: 'Type' },
  { key: 'raw_material', label: 'Material', format: (v: any) => v?.name || '-' },
  { key: 'quantity', label: 'Quantity' },
  { key: 'notes', label: 'Notes' },
]

const typeFilters = [
  {
    key: 'type',
    label: 'Type',
    type: 'select' as const,
    options: [
      { value: 'in', label: 'Stock In' },
      { value: 'out', label: 'Stock Out' },
      { value: 'adjustment', label: 'Adjustment' },
      { value: 'transfer', label: 'Transfer' },
    ],
  },
]

const { currentPage, perPage, filters, selected, load, onSearch, onPage, setFilter, toggleSelect, clearSelection, exportCsv } = useList(async (p) => {
  const q = p as unknown as Record<string, any>
  await inventoryStore.fetchStockMovements({
    page: q.page,
    per_page: q.per_page,
    search: q.search || undefined,
    type: q.type || undefined,
  })
})

onMounted(async () => {
  await load()
})

const handleReset = () => {
  Object.keys(filters.value).forEach((k) => delete filters.value[k])
  currentPage.value = 1
  load()
}

const toggleSelectAll = () => {
  const ids = inventoryStore.stockMovements.map((m: any) => m.id)
  const all = ids.length > 0 && ids.every((id) => selected.value.includes(id))
  if (all) clearSelection()
  else selected.value = [...new Set([...selected.value, ...ids])]
}

const flatRows = (rows: any[]) => rows.map((m: any) => ({
  date: m.created_at ? new Date(m.created_at).toLocaleDateString() : '',
  type: m.type,
  material: m.raw_material?.name || '',
  quantity: m.quantity,
  notes: m.notes || '',
}))

const exportSelected = () => {
  exportCsv(flatRows(inventoryStore.stockMovements.filter((m: any) => selected.value.includes(m.id))), 'stock-movements')
}

const exportAll = () => {
  exportCsv(flatRows(inventoryStore.stockMovements), 'stock-movements')
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Stock Movements" subtitle="Track all inventory movements and adjustments">
      <template #actions>
        <button @click="exportAll" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Export</button>
      </template>
    </PageHeader>

    <FilterBar :filters="typeFilters" :values="filters" @change="setFilter" @reset="handleReset" />

    <BulkBar :count="selected.length" @export="exportSelected" @clear="clearSelection" />

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="inventoryStore.stockMovements"
        :loading="inventoryStore.loading"
        :total-items="inventoryStore.totalMovements"
        :current-page="currentPage"
        :per-page="perPage"
        selectable
        :selected-ids="selected"
        @search="onSearch"
        @update:current-page="onPage"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-type="{ row }">
          <span
            :class="{
              'bg-green-100 text-green-800': row.type === 'in',
              'bg-red-100 text-red-800': row.type === 'out',
              'bg-yellow-100 text-yellow-800': row.type === 'adjustment',
              'bg-blue-100 text-blue-800': row.type === 'transfer',
            }"
            class="px-2 py-1 rounded-full text-xs font-medium capitalize"
          >
            {{ row.type.replace('_', ' ') }}
          </span>
        </template>
      </DataTable>
    </div>
  </div>
</template>
