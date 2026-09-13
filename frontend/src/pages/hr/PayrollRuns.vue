<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useHrStore } from '@/stores/hr'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import DataTable from '@/components/DataTable.vue'
import BulkBar from '@/components/BulkBar.vue'
import StatusBadge from '@/components/StatusBadge.vue'

const hrStore = useHrStore()

const statusFilter = ref('')
const search = ref('')
const selectedIds = ref<number[]>([])

const filters = [
  { key: 'status', label: 'Status', type: 'select' as const, options: [
    { value: 'draft', label: 'Draft' },
    { value: 'approved', label: 'Approved' },
    { value: 'paid', label: 'Paid' },
  ]},
]

const filterValues = computed(() => ({ status: statusFilter.value }))

const columns = [
  { key: 'period', label: 'Period' },
  { key: 'total_amount', label: 'Total Amount' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' },
]

const filteredRuns = computed(() => {
  let rows = hrStore.payrollRuns as any[]
  if (statusFilter.value) rows = rows.filter(r => r.status === statusFilter.value)
  if (search.value) {
    const q = search.value.toLowerCase()
    rows = rows.filter(r => String(r.id).includes(q) || String(r.status).toLowerCase().includes(q))
  }
  return rows
})

onMounted(async () => {
  await hrStore.fetchPayrollRuns()
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(Number(value || 0))
}

const handleFilterChange = (key: string, value: any) => {
  if (key === 'status') statusFilter.value = value
}

const resetFilters = () => {
  statusFilter.value = ''
}

const handleSearch = (query: string) => {
  search.value = query
}

const toggleSelect = (id: number) => {
  const i = selectedIds.value.indexOf(id)
  if (i >= 0) selectedIds.value.splice(i, 1)
  else selectedIds.value.push(id)
}

const toggleSelectAll = () => {
  const all = filteredRuns.value.map(r => r.id)
  if (all.every(id => selectedIds.value.includes(id))) selectedIds.value = []
  else selectedIds.value = all
}

const exportCsv = () => {
  const rows = filteredRuns.value.filter(r => selectedIds.value.includes(r.id))
  const data = rows.length ? rows : filteredRuns.value
  if (!data.length) return
  const headers = ['id', 'period_start', 'period_end', 'total_amount', 'status']
  const esc = (v: any) => `"${String(v ?? '').replace(/"/g, '""')}"`
  const csv = [headers.join(','), ...data.map((r: any) => headers.map(h => esc(r[h])).join(','))].join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `payroll-runs-${new Date().toISOString().slice(0, 10)}.csv`
  a.click()
  URL.revokeObjectURL(url)
}

const handleApprove = async (id: number) => {
  if (confirm('Approve this payroll run?')) {
    await hrStore.approvePayrollRun(id)
  }
}

const handlePay = async (id: number) => {
  if (confirm('Mark this payroll run as paid?')) {
    await hrStore.payPayrollRun(id)
  }
}

const handleExport = async (id: number) => {
  const blob = await hrStore.exportPayrollRun(id)
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `payroll-${id}.csv`
  a.click()
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Payroll Runs" subtitle="Manage payroll processing and payment approval" />

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="resetFilters" />

    <BulkBar :count="selectedIds.length" @export="exportCsv" @clear="selectedIds = []" />

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="filteredRuns"
        :loading="hrStore.loading"
        :total-items="filteredRuns.length"
        :current-page="1"
        :per-page="filteredRuns.length || 15"
        selectable
        :selected-ids="selectedIds"
        @search="handleSearch"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-period="{ row }">
          <span class="text-sm text-gray-900 dark:text-white">{{ new Date(row.period_start).toLocaleDateString() }} - {{ new Date(row.period_end).toLocaleDateString() }}</span>
        </template>
        <template #cell-total_amount="{ row }">
          <span class="text-sm text-gray-900 dark:text-white">{{ formatCurrency(row.total_amount) }}</span>
        </template>
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" type="payment" />
        </template>
        <template #cell-actions="{ row }">
          <div class="flex gap-2">
            <button
              v-if="row.status === 'draft'"
              @click="handleApprove(row.id)"
              class="text-primary-600 hover:text-primary-800 text-sm"
            >
              Approve
            </button>
            <button
              v-if="row.status === 'approved'"
              @click="handlePay(row.id)"
              class="text-primary-600 hover:text-primary-800 text-sm"
            >
              Pay
            </button>
            <button
              @click="handleExport(row.id)"
              class="text-gray-600 hover:text-gray-800 dark:text-slate-400 text-sm"
            >
              Export
            </button>
          </div>
        </template>
      </DataTable>
    </div>
  </div>
</template>
