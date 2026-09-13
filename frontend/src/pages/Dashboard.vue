<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useReportsStore } from '@/stores/reports'
import { useAuthStore } from '@/stores/auth'
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
  Filler,
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const reportsStore = useReportsStore()
const authStore = useAuthStore()

onMounted(async () => {
  await reportsStore.fetchDashboard()
  if (reportsStore.dashboard?.sales.chart) {
    salesChartData.value.labels = reportsStore.dashboard.sales.chart.map(d => d.date)
    salesChartData.value.datasets[0].data = reportsStore.dashboard.sales.chart.map(d => d.amount)
  }
})

const salesChartData = ref({
  labels: [] as string[],
  datasets: [
    {
      label: 'Sales',
      data: [] as number[],
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59, 130, 246, 0.08)',
      fill: true,
      tension: 0.4,
      borderWidth: 2,
      pointRadius: 0,
      pointHoverRadius: 4,
    },
  ],
})

const salesChartOptions: any = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1e293b',
      titleColor: '#f8fafc',
      bodyColor: '#cbd5e1',
      borderColor: '#334155',
      borderWidth: 1,
      cornerRadius: 8,
      padding: 12,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: { color: '#94a3b8', callback: (v: string | number) => '$' + Number(v).toLocaleString() },
    },
    x: {
      grid: { display: false },
      ticks: { color: '#94a3b8', maxTicksLimit: 7 },
    },
  },
}

const getGreeting = () => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Good morning'
  if (hour < 18) return 'Good afternoon'
  return 'Good evening'
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
  }).format(value)
}

</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ getGreeting() }}, {{ authStore.user?.name?.split(' ')[0] }}
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
          Here's what's happening with your business today.
        </p>
      </div>
      <div class="flex gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 dark:bg-primary-900/20 px-3 py-1.5 text-xs font-medium text-primary-700 dark:text-primary-300">
          <span class="w-1.5 h-1.5 bg-primary-500 rounded-full animate-pulse"></span>
          Live
        </span>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="reportsStore.loading" class="flex justify-center py-16">
      <div class="flex flex-col items-center gap-3">
        <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        <span class="text-sm text-gray-500">Loading dashboard...</span>
      </div>
    </div>

    <template v-else-if="reportsStore.dashboard">
      <!-- Stat Cards -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Sales -->
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Total Sales</p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ formatCurrency(reportsStore.dashboard.sales.total) }}
              </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-xl shadow-lg shadow-blue-500/20">
              💰
            </div>
          </div>
          <div class="mt-3 flex items-center gap-1.5">
            <span :class="reportsStore.dashboard.sales.change >= 0 ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20' : 'text-red-600 bg-red-50 dark:bg-red-900/20'" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium">
              <svg v-if="reportsStore.dashboard.sales.change >= 0" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 17l9.2-9.2M17 17V7H7"/>
              </svg>
              <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 7l-9.2 9.2M7 7v10h10"/>
              </svg>
              {{ Math.abs(reportsStore.dashboard.sales.change) }}%
            </span>
            <span class="text-xs text-gray-400">vs last month</span>
          </div>
        </div>

        <!-- Production -->
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Active Production</p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ reportsStore.dashboard.production.active_orders }}
              </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-xl shadow-lg shadow-amber-500/20">
              🏭
            </div>
          </div>
          <div class="mt-3">
            <span class="text-xs text-gray-400">
              {{ reportsStore.dashboard.production.completed_today }} completed today
            </span>
          </div>
        </div>

        <!-- Inventory -->
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Inventory Value</p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ formatCurrency(reportsStore.dashboard.inventory.total_value) }}
              </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center text-xl shadow-lg shadow-emerald-500/20">
              📦
            </div>
          </div>
          <div class="mt-3">
            <span :class="reportsStore.dashboard.inventory.low_stock_items > 0 ? 'text-amber-600' : 'text-gray-400'" class="text-xs font-medium">
              {{ reportsStore.dashboard.inventory.low_stock_items }} low stock items
            </span>
          </div>
        </div>

        <!-- QC -->
        <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Pending QC</p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ reportsStore.dashboard.production.pending_qc }}
              </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center text-xl shadow-lg shadow-violet-500/20">
              ✅
            </div>
          </div>
          <div class="mt-3">
            <span class="text-xs text-gray-400">Requires attention</span>
          </div>
        </div>
      </div>

      <!-- Chart + Recent Orders -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Sales Chart -->
        <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sales Trend</h3>
            <span class="text-xs text-gray-400">Last 30 days</span>
          </div>
          <div class="h-64">
            <Line :data="salesChartData" :options="salesChartOptions" />
          </div>
        </div>

        <!-- Recent Orders -->
        <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
            <router-link to="/sales/orders" class="text-xs font-medium text-primary-600 hover:text-primary-500">View all</router-link>
          </div>
          <div class="space-y-3">
            <div
              v-for="order in reportsStore.dashboard.recent_orders"
              :key="order.id"
              class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-slate-700/50 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors cursor-pointer"
            >
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-sm font-bold text-primary-700 dark:text-primary-300">
                  {{ order.order_number?.slice(-2) }}
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ order.order_number }}</p>
                  <p class="text-xs text-gray-500 dark:text-slate-400">{{ order.customer_name }}</p>
                </div>
              </div>
              <div class="text-right">
                <StatusBadge :status="order.status" type="order" />
                <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">{{ formatCurrency(order.total) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Business Hub -->
      <div class="card card-pad">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-base font-semibold text-slate-900 dark:text-white">Business Hub</h3>
          <span class="text-xs text-slate-400">Every module, one click away</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
          <router-link v-for="m in [
            { label: 'Products', to: '/catalog/products', icon: '📦' },
            { label: 'Orders', to: '/sales/orders', icon: '🧾' },
            { label: 'Production', to: '/production/orders', icon: '🏭' },
            { label: 'Inventory', to: '/inventory/raw-materials', icon: '📊' },
            { label: 'Shipments', to: '/logistics/shipments', icon: '🚚' },
            { label: 'Invoices', to: '/finance/invoices', icon: '💳' },
            { label: 'Leads', to: '/crm/leads', icon: '🤝' },
            { label: 'Suppliers', to: '/procurement/suppliers', icon: '🏷️' },
            { label: 'Payroll', to: '/hr/payroll', icon: '👥' },
            { label: 'Reports', to: '/reports/sales', icon: '📈' },
            { label: 'Companies', to: '/companies', icon: '🏢' },
            { label: 'Billing', to: '/settings/billing', icon: '💎' },
          ]" :key="m.label" :to="m.to" class="group rounded-xl border border-slate-200/70 dark:border-slate-800 p-4 text-center hover:border-primary-300 hover:shadow-card transition-all">
            <div class="text-2xl">{{ m.icon }}</div>
            <p class="mt-2 text-[13px] font-semibold text-slate-700 dark:text-slate-200">{{ m.label }}</p>
          </router-link>
        </div>
      </div>

      <!-- Top Products -->
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Products</h3>
          <router-link to="/catalog/products" class="text-xs font-medium text-primary-600 hover:text-primary-500">View all</router-link>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
          <div
            v-for="item in reportsStore.dashboard.top_products"
            :key="item.product.id"
            class="group text-center p-5 rounded-2xl bg-gray-50 dark:bg-slate-700/50 hover:bg-primary-50 dark:hover:bg-primary-900/10 border border-transparent hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200 cursor-pointer"
          >
            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/30 dark:to-primary-800/30 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
              🧶
            </div>
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ item.product.name }}</p>
            <p class="mt-1 text-lg font-bold text-primary-600 dark:text-primary-400">{{ item.quantity }}</p>
            <p class="text-xs text-gray-400">units</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
