<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useReportsStore } from '@/stores/reports'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import StatStrip from '@/components/StatStrip.vue'
import DataTable from '@/components/DataTable.vue'
import BulkBar from '@/components/BulkBar.vue'

const reportsStore = useReportsStore()

const stockFilter = ref('')
const selectedIds = ref<number[]>([])

const filters = [
  { key: 'stock', label: 'Stock Status', type: 'select' as const, options: [
    { value: 'low', label: 'Low Stock' },
    { value: 'out', label: 'Out of Stock' },
    { value: 'normal', label: 'Normal' },
  ]},
]

const filterValues = computed(() => ({ stock: stockFilter.value }))

const columns = [
  { key: 'name', label: 'Item' },
  { key: 'stock_quantity', label: 'Stock' },
  { key: 'value', label: 'Value' },
]

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  await reportsStore.fetchInventorySummary()
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(Number(value || 0))
}

const totalItems = computed(() => reportsStore.inventorySummary?.total_items ?? reportsStore.inventorySummary?.total_variants ?? reportsStore.inventorySummary?.total_raw_materials ?? 0)
const totalValue = computed(() => reportsStore.inventorySummary?.total_value ?? 0)
const lowStock = computed(() => reportsStore.inventorySummary?.low_stock_items ?? reportsStore.inventorySummary?.low_stock_count ?? 0)
const outOfStock = computed(() => reportsStore.inventorySummary?.out_of_stock ?? 0)
const turnover = computed(() => reportsStore.inventorySummary?.turnover_rate ?? 0)

const inventoryStats = computed(() => [
  { label: 'Total Items', value: totalItems.value },
  { label: 'Total Value', value: formatCurrency(totalValue.value) },
  { label: 'Low Stock', value: lowStock.value },
  { label: outOfStock.value || !reportsStore.inventorySummary?.turnover_rate ? 'Out of Stock' : 'Turnover', value: outOfStock.value || !reportsStore.inventorySummary?.turnover_rate ? outOfStock.value : `${turnover.value}x` },
])

const allRows = computed(() => {
  const top = reportsStore.inventorySummary?.top_items ?? []
  if (Array.isArray(top) && top.length) {
    return top.map((r: any) => ({ id: r.id, name: r.name, stock_quantity: r.stock_quantity ?? 0, value: r.value ?? 0 }))
  }
  const byCat = reportsStore.inventorySummary?.value_by_category ?? {}
  if (byCat && typeof byCat === 'object' && Object.keys(byCat).length) {
    return Object.entries(byCat).map(([name, value], i) => ({ id: i + 1, name, stock_quantity: '-', value }))
  }
  return []
})

const itemRows = computed(() => {
  if (!stockFilter.value) return allRows.value
  return allRows.value.filter((r: any) => {
    if (stockFilter.value === 'out') return Number(r.stock_quantity) <= 0
    if (stockFilter.value === 'low') return Number(r.stock_quantity) > 0 && Number(r.stock_quantity) <= 10
    return Number(r.stock_quantity) > 10
  })
})

const handleFilterChange = (key: string, value: any) => {
  if (key === 'stock') stockFilter.value = value
}

const resetFilters = () => {
  stockFilter.value = ''
}

const toggleSelect = (id: number) => {
  const i = selectedIds.value.indexOf(id)
  if (i >= 0) selectedIds.value.splice(i, 1)
  else selectedIds.value.push(id)
}

const toggleSelectAll = () => {
  const all = itemRows.value.map(r => r.id)
  if (all.every(id => selectedIds.value.includes(id))) selectedIds.value = []
  else selectedIds.value = all
}

const exportCsv = () => {
  const rows = itemRows.value.filter(r => selectedIds.value.includes(r.id))
  const data = rows.length ? rows : itemRows.value
  if (!data.length) return
  const headers = ['id', 'name', 'stock_quantity', 'value']
  const esc = (v: any) => `"${String(v ?? '').replace(/"/g, '""')}"`
  const csv = [headers.join(','), ...data.map((r: any) => headers.map(h => esc(r[h])).join(','))].join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `inventory-report-${new Date().toISOString().slice(0, 10)}.csv`
  a.click()
  URL.revokeObjectURL(url)
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Inventory Report" subtitle="Analyze inventory levels, value, and turnover">
      <template #actions>
        <button @click="loadData" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Refresh</button>
        <button @click="exportCsv" class="rounded-lg border border-gray-300 dark:border-slate-600 px-4 py-2.5 text-sm font-medium">Export CSV</button>
      </template>
    </PageHeader>

    <StatStrip :stats="inventoryStats" :loading="reportsStore.loading" />

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="resetFilters" />

    <BulkBar :count="selectedIds.length" @export="exportCsv" @clear="selectedIds = []" />

    <DataTable
      :columns="columns"
      :data="itemRows"
      :loading="reportsStore.loading"
      :total-items="itemRows.length"
      :current-page="1"
      :per-page="itemRows.length || 15"
      selectable
      :selected-ids="selectedIds"
      @toggle-select="toggleSelect"
      @toggle-select-all="toggleSelectAll"
    >
      <template #cell-value="{ value }">
        {{ typeof value === 'number' ? formatCurrency(value) : value }}
      </template>
    </DataTable>

    <div v-if="reportsStore.inventorySummary?.value_by_category" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Stock Value by Category</h3>
      <div class="space-y-4">
        <div v-for="(value, category) in reportsStore.inventorySummary.value_by_category" :key="category" class="flex items-center justify-between">
          <span class="text-gray-700 dark:text-slate-300">{{ category }}</span>
          <span class="font-medium text-gray-900 dark:text-white">{{ formatCurrency(value as number) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
