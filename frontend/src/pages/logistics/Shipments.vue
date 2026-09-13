<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useLogisticsStore } from '@/stores/logistics'
import DataTable from '@/components/DataTable.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import BulkBar from '@/components/BulkBar.vue'
import KanbanBoard from '@/components/KanbanBoard.vue'

const router = useRouter()
const logisticsStore = useLogisticsStore()

const search = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const selectedIds = ref<number[]>([])
const viewMode = ref<'table' | 'kanban'>('table')
const filterValues = ref<Record<string, any>>({ status: '' })

const columns = [
  { key: 'shipment_number', label: 'Shipment #', sortable: true },
  { key: 'carrier', label: 'Carrier' },
  { key: 'tracking_number', label: 'Tracking #', format: (v: string) => v || '-' },
  { key: 'status', label: 'Status' },
  { key: 'shipped_at', label: 'Shipped', format: (v: string) => v ? new Date(v).toLocaleDateString() : '-' },
  { key: 'delivered_at', label: 'Delivered', format: (v: string) => v ? new Date(v).toLocaleDateString() : '-' },
]

const filters = [
  { key: 'status', label: 'Status', type: 'select' as const, options: [
    { value: 'pending', label: 'Pending' },
    { value: 'dispatched', label: 'Dispatched' },
    { value: 'in_transit', label: 'In Transit' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'returned', label: 'Returned' },
  ] },
]

const kanbanColumns = [
  { key: 'pending', label: 'Pending' },
  { key: 'dispatched', label: 'Dispatched' },
  { key: 'in_transit', label: 'In Transit' },
  { key: 'delivered', label: 'Delivered' },
  { key: 'returned', label: 'Returned' },
]

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  await logisticsStore.fetchShipments({
    page: currentPage.value,
    per_page: perPage.value,
    search: search.value,
    status: filterValues.value.status || undefined,
  })
}

const handleSearch = (query: string) => {
  search.value = query
  currentPage.value = 1
  loadData()
}

const handlePageChange = (page: number) => {
  currentPage.value = page
  loadData()
}

const handleFilterChange = (key: string, value: any) => {
  filterValues.value[key] = value
  currentPage.value = 1
  loadData()
}

const handleFilterReset = () => {
  filterValues.value = { status: '' }
  currentPage.value = 1
  loadData()
}

const toggleSelect = (id: number) => {
  const i = selectedIds.value.indexOf(id)
  if (i === -1) selectedIds.value.push(id)
  else selectedIds.value.splice(i, 1)
}

const toggleSelectAll = () => {
  const all = logisticsStore.shipments.map((r: any) => r.id)
  const allSelected = all.length > 0 && all.every((id) => selectedIds.value.includes(id))
  selectedIds.value = allSelected ? [] : all
}

const exportCsv = () => {
  const rows = logisticsStore.shipments.filter((r: any) => selectedIds.value.length === 0 || selectedIds.value.includes(r.id))
  const header = ['shipment_number', 'carrier', 'tracking_number', 'status', 'shipped_at', 'delivered_at']
  const lines = [header.join(',')]
  for (const r of rows as any[]) {
    lines.push([r.shipment_number, r.carrier, r.tracking_number || r.tracking_no || '', r.status, r.shipped_at || '', r.delivered_at || r.actual_arrival || ''].map((v) => `"${String(v ?? '').replace(/"/g, '""')}"`).join(','))
  }
  const blob = new Blob([lines.join('\n')], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'shipments.csv'
  a.click()
  URL.revokeObjectURL(url)
}

const viewShipment = (id: number) => {
  router.push(`/logistics/shipments/${id}`)
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Shipments" subtitle="Track and manage all outgoing shipments">
      <template #actions>
        <div class="flex rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
          <button @click="viewMode = 'table'" :class="['px-3 py-2 text-sm font-medium', viewMode === 'table' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300']">Table</button>
          <button @click="viewMode = 'kanban'" :class="['px-3 py-2 text-sm font-medium', viewMode === 'kanban' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300']">Kanban</button>
        </div>
      </template>
    </PageHeader>

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="handleFilterReset" />

    <BulkBar :count="selectedIds.length" @export="exportCsv" @clear="selectedIds = []" />

    <div v-if="viewMode === 'table'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="logisticsStore.shipments"
        :loading="logisticsStore.loading"
        :total-items="logisticsStore.totalShipments"
        :current-page="currentPage"
        :per-page="perPage"
        selectable
        :selected-ids="selectedIds"
        @search="handleSearch"
        @update:current-page="handlePageChange"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-shipment_number="{ row }">
          <button
            @click="viewShipment(row.id)"
            class="text-primary-600 hover:text-primary-800 font-medium"
          >
            {{ row.shipment_number }}
          </button>
        </template>
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" type="shipment" />
        </template>
      </DataTable>
    </div>

    <KanbanBoard
      v-else
      :columns="kanbanColumns"
      :items="logisticsStore.shipments"
      title-key="shipment_number"
      subtitle-key="carrier"
      badge-type="shipment"
      @open="viewShipment"
    />
  </div>
</template>
