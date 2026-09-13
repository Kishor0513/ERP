<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useReportsStore } from '@/stores/reports'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import StatStrip from '@/components/StatStrip.vue'
import DataTable from '@/components/DataTable.vue'
import BulkBar from '@/components/BulkBar.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
)

const reportsStore = useReportsStore()

const dateRange = ref({
  start: new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0],
  end: new Date().toISOString().split('T')[0],
})

const channelFilter = ref('')
const statusFilter = ref('')
const selectedIds = ref<number[]>([])

const filters = [
  { key: 'channel', label: 'Channel', type: 'select' as const, options: [
    { value: 'online', label: 'Online' },
    { value: 'wholesale', label: 'Wholesale' },
    { value: 'retail', label: 'Retail' },
    { value: 'direct', label: 'Direct' },
  ]},
  { key: 'status', label: 'Status', type: 'select' as const, options: [
    { value: 'pending', label: 'Pending' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'shipped', label: 'Shipped' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'cancelled', label: 'Cancelled' },
  ]},
]

const filterValues = computed(() => ({ channel: channelFilter.value, status: statusFilter.value }))

const columns = [
  { key: 'status', label: 'Status' },
  { key: 'count', label: 'Orders' },
  { key: 'revenue', label: 'Revenue' },
]

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  await reportsStore.fetchSalesSummary({
    start_date: dateRange.value.start,
    end_date: dateRange.value.end,
  })
  buildChart()
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(Number(value || 0))
}

const totalRevenue = computed(() => reportsStore.salesSummary?.total_revenue ?? reportsStore.salesSummary?.total_sales ?? 0)
const totalOrders = computed(() => reportsStore.salesSummary?.total_orders ?? 0)
const avgOrderValue = computed(() => reportsStore.salesSummary?.average_order_value ?? reportsStore.salesSummary?.avg_order_value ?? 0)
const conversionRate = computed(() => reportsStore.salesSummary?.conversion_rate ?? 0)

const salesStats = computed(() => [
  { label: 'Total Revenue', value: formatCurrency(totalRevenue.value) },
  { label: 'Total Orders', value: totalOrders.value },
  { label: 'Avg Order Value', value: formatCurrency(avgOrderValue.value) },
  { label: 'Conversion Rate', value: `${conversionRate.value}%` },
])

const statusRows = computed(() => {
  const byStatus = reportsStore.salesSummary?.by_status ?? []
  const arr = Array.isArray(byStatus) ? byStatus : []
  return arr.map((r: any, i: number) => ({
    id: i + 1,
    status: r.status,
    count: r.count ?? 0,
    revenue: r.revenue ?? 0,
  })).filter((r: any) => !statusFilter.value || r.status === statusFilter.value)
})

const chartData = ref({
  labels: [] as string[],
  datasets: [
    {
      label: 'Sales',
      data: [] as number[],
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      fill: true,
    },
  ],
})

const buildChart = () => {
  if (statusRows.value.length) {
    chartData.value = {
      labels: statusRows.value.map((r: any) => r.status),
      datasets: [{ label: 'Revenue', data: statusRows.value.map((r: any) => Number(r.revenue)), borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true }],
    }
  }
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
    },
  },
}

const handleFilterChange = (key: string, value: any) => {
  if (key === 'channel') channelFilter.value = value
  if (key === 'status') statusFilter.value = value
  if (key === 'channel') loadData()
}

const resetFilters = () => {
  channelFilter.value = ''
  statusFilter.value = ''
  loadData()
}

const toggleSelect = (id: number) => {
  const i = selectedIds.value.indexOf(id)
  if (i >= 0) selectedIds.value.splice(i, 1)
  else selectedIds.value.push(id)
}

const toggleSelectAll = () => {
  const all = statusRows.value.map(r => r.id)
  if (all.every(id => selectedIds.value.includes(id))) selectedIds.value = []
  else selectedIds.value = all
}

const exportCsv = () => {
  const rows = statusRows.value.filter(r => selectedIds.value.includes(r.id))
  const data = rows.length ? rows : statusRows.value
  if (!data.length) return
  const headers = ['status', 'count', 'revenue']
  const esc = (v: any) => `"${String(v ?? '').replace(/"/g, '""')}"`
  const csv = [headers.join(','), ...data.map((r: any) => headers.map(h => esc(r[h])).join(','))].join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `sales-report-${new Date().toISOString().slice(0, 10)}.csv`
  a.click()
  URL.revokeObjectURL(url)
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Sales Report" subtitle="Analyze sales performance and revenue trends">
      <template #actions>
        <button @click="loadData" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Generate Report</button>
        <button @click="exportCsv" class="rounded-lg border border-gray-300 dark:border-slate-600 px-4 py-2.5 text-sm font-medium">Export CSV</button>
      </template>
    </PageHeader>

    <StatStrip :stats="salesStats" :loading="reportsStore.loading" />

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="resetFilters">
      <template #actions>
        <input v-model="dateRange.start" type="date" class="input !py-2" />
        <input v-model="dateRange.end" type="date" class="input !py-2" />
      </template>
    </FilterBar>

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <div v-if="reportsStore.salesSummary" class="h-64">
        <Line :data="chartData" :options="chartOptions" />
      </div>
    </div>

    <BulkBar :count="selectedIds.length" @export="exportCsv" @clear="selectedIds = []" />

    <DataTable
      :columns="columns"
      :data="statusRows"
      :loading="reportsStore.loading"
      :total-items="statusRows.length"
      :current-page="1"
      :per-page="statusRows.length || 15"
      :searchable="false"
      selectable
      :selected-ids="selectedIds"
      @toggle-select="toggleSelect"
      @toggle-select-all="toggleSelectAll"
    >
      <template #cell-status="{ row }">
        <StatusBadge :status="row.status" />
      </template>
      <template #cell-revenue="{ value }">
        {{ formatCurrency(value) }}
      </template>
    </DataTable>
  </div>
</template>
