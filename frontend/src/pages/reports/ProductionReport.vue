<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useReportsStore } from '@/stores/reports'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import StatStrip from '@/components/StatStrip.vue'
import DataTable from '@/components/DataTable.vue'
import BulkBar from '@/components/BulkBar.vue'
import StatusBadge from '@/components/StatusBadge.vue'

const reportsStore = useReportsStore()

const dateRange = ref({
  start: new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0],
  end: new Date().toISOString().split('T')[0],
})

const statusFilter = ref('')
const selectedIds = ref<number[]>([])

const filters = [
  { key: 'status', label: 'Status', type: 'select' as const, options: [
    { value: 'pending', label: 'Pending' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
  ]},
]

const filterValues = computed(() => ({ status: statusFilter.value }))

const columns = [
  { key: 'artisan', label: 'Artisan' },
  { key: 'count', label: 'Units' },
  { key: 'status', label: 'Status' },
]

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  await reportsStore.fetchProductionSummary({
    start_date: dateRange.value.start,
    end_date: dateRange.value.end,
  })
}

const totalOrders = computed(() => reportsStore.productionSummary?.total_orders ?? 0)
const completed = computed(() => reportsStore.productionSummary?.completed ?? 0)
const inProgress = computed(() => reportsStore.productionSummary?.in_progress ?? 0)
const qcRate = computed(() => reportsStore.productionSummary?.qc_pass_rate ?? reportsStore.productionSummary?.defect_rate ?? 0)

const productionStats = computed(() => [
  { label: 'Total Orders', value: totalOrders.value },
  { label: 'Completed', value: completed.value },
  { label: 'In Progress', value: reportsStore.productionSummary?.in_progress ?? reportsStore.productionSummary?.avg_production_time ?? inProgress.value },
  { label: reportsStore.productionSummary?.qc_pass_rate !== undefined ? 'QC Pass Rate' : 'Defect Rate', value: `${Number(qcRate.value).toFixed(1)}%` },
])

const throughputRows = computed(() => {
  const t = reportsStore.productionSummary?.throughput_by_artisan ?? {}
  if (t && typeof t === 'object' && Object.keys(t).length) {
    return Object.entries(t).map(([artisan, count], i) => ({ id: i + 1, artisan, count, status: 'completed' }))
  }
  const rows = []
  if (totalOrders.value) {
    rows.push(
      { id: 1, artisan: 'In Progress', count: inProgress.value, status: 'in_progress' },
      { id: 2, artisan: 'Completed', count: completed.value, status: 'completed' },
    )
  }
  return rows.filter(r => !statusFilter.value || r.status === statusFilter.value)
})

const handleFilterChange = (key: string, value: any) => {
  if (key === 'status') statusFilter.value = value
}

const resetFilters = () => {
  statusFilter.value = ''
}

const toggleSelect = (id: number) => {
  const i = selectedIds.value.indexOf(id)
  if (i >= 0) selectedIds.value.splice(i, 1)
  else selectedIds.value.push(id)
}

const toggleSelectAll = () => {
  const all = throughputRows.value.map(r => r.id)
  if (all.every(id => selectedIds.value.includes(id))) selectedIds.value = []
  else selectedIds.value = all
}

const exportCsv = () => {
  const rows = throughputRows.value.filter(r => selectedIds.value.includes(r.id))
  const data = rows.length ? rows : throughputRows.value
  if (!data.length) return
  const headers = ['artisan', 'count', 'status']
  const esc = (v: any) => `"${String(v ?? '').replace(/"/g, '""')}"`
  const csv = [headers.join(','), ...data.map((r: any) => headers.map(h => esc(r[h])).join(','))].join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `production-report-${new Date().toISOString().slice(0, 10)}.csv`
  a.click()
  URL.revokeObjectURL(url)
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Production Report" subtitle="Monitor production efficiency and artisan performance">
      <template #actions>
        <button @click="loadData" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Generate Report</button>
        <button @click="exportCsv" class="rounded-lg border border-gray-300 dark:border-slate-600 px-4 py-2.5 text-sm font-medium">Export CSV</button>
      </template>
    </PageHeader>

    <StatStrip :stats="productionStats" :loading="reportsStore.loading" />

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="resetFilters">
      <template #actions>
        <input v-model="dateRange.start" type="date" class="input !py-2" />
        <input v-model="dateRange.end" type="date" class="input !py-2" />
      </template>
    </FilterBar>

    <BulkBar :count="selectedIds.length" @export="exportCsv" @clear="selectedIds = []" />

    <DataTable
      :columns="columns"
      :data="throughputRows"
      :loading="reportsStore.loading"
      :total-items="throughputRows.length"
      :current-page="1"
      :per-page="throughputRows.length || 15"
      :searchable="false"
      selectable
      :selected-ids="selectedIds"
      @toggle-select="toggleSelect"
      @toggle-select-all="toggleSelectAll"
    >
      <template #cell-status="{ row }">
        <StatusBadge :status="row.status" type="production" />
      </template>
    </DataTable>
  </div>
</template>
