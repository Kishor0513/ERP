<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSalesStore } from '@/stores/sales'
import StatusBadge from '@/components/StatusBadge.vue'
import PageHeader from '@/components/PageHeader.vue'
import { useList } from '@/composables/useList'

const route = useRoute()
const router = useRouter()
const salesStore = useSalesStore()

const order = ref<any>(null)
const activeTab = ref('items')

const { exportCsv } = useList(async () => {})

onMounted(async () => {
  const id = Number(route.params.id)
  order.value = await salesStore.fetchSalesOrder(id)
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(value)
}

const handleShip = async () => {
  await salesStore.shipOrder(order.value.id, {})
  order.value = await salesStore.fetchSalesOrder(order.value.id)
}

const handleCancel = async () => {
  if (confirm('Are you sure you want to cancel this order?')) {
    await salesStore.cancelOrder(order.value.id)
    order.value = await salesStore.fetchSalesOrder(order.value.id)
  }
}

const handleExport = () => {
  if (!order.value) return
  exportCsv((order.value.items || []).map((i: any) => ({ product: i.product?.name || '', quantity: i.quantity, unit_price: i.unit_price, total: i.total })), `order-${order.value.order_number}`)
}
</script>

<template>
  <div class="space-y-6" v-if="order">
    <PageHeader :title="order.order_number" :subtitle="order.customer_name">
      <template #actions>
        <button @click="router.back()" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Back</button>
        <button @click="handleExport" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Export CSV</button>
        <button
          v-if="order.status === 'confirmed' || order.status === 'processing'"
          @click="handleShip"
          class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500"
        >
          Ship Order
        </button>
        <button
          v-if="order.status !== 'cancelled' && order.status !== 'delivered'"
          @click="handleCancel"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50"
        >
          Cancel Order
        </button>
      </template>
    </PageHeader>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Status</p>
        <StatusBadge :status="order.status" type="order" />
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Channel</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white capitalize">{{ order.channel }}</p>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Total</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(order.total) }}</p>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Date</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ new Date(order.created_at).toLocaleDateString() }}</p>
      </div>
    </div>

    <div class="border-b border-gray-200 dark:border-slate-700">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in ['items', 'shipping', 'timeline']"
          :key="tab"
          @click="activeTab = tab"
          :class="[
            activeTab === tab
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm capitalize',
          ]"
        >
          {{ tab }}
        </button>
      </nav>
    </div>

    <div v-if="activeTab === 'items'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
        <thead class="bg-gray-50 dark:bg-slate-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Unit Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Total</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
          <tr v-for="item in order.items" :key="item.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ item.product?.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ item.quantity }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatCurrency(item.unit_price) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatCurrency(item.total) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="activeTab === 'shipping'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Shipping Address</h3>
      <p class="text-gray-600 dark:text-slate-300">
        {{ order.shipping_address.street }}, {{ order.shipping_address.city }}, {{ order.shipping_address.state }} {{ order.shipping_address.postal_code }}
      </p>
    </div>

    <div v-if="activeTab === 'timeline'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Order Timeline</h3>
      <p class="text-gray-500 dark:text-slate-400">Timeline will be shown here</p>
    </div>
  </div>
</template>
