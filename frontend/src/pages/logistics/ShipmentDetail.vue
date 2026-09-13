<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLogisticsStore } from '@/stores/logistics'
import StatusBadge from '@/components/StatusBadge.vue'

const route = useRoute()
const router = useRouter()
const logisticsStore = useLogisticsStore()

const shipment = ref<any>(null)

onMounted(async () => {
  const id = Number(route.params.id)
  shipment.value = await logisticsStore.fetchShipment(id)
})

const handleDispatch = async () => {
  await logisticsStore.dispatchShipment(shipment.value.id)
  shipment.value = await logisticsStore.fetchShipment(shipment.value.id)
}

const handleDeliver = async () => {
  if (confirm('Mark shipment as delivered?')) {
    await logisticsStore.deliverShipment(shipment.value.id)
    shipment.value = await logisticsStore.fetchShipment(shipment.value.id)
  }
}
</script>

<template>
  <div class="space-y-6" v-if="shipment">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <button
          @click="router.back()"
          class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mb-2"
        >
          ← Back to Shipments
        </button>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ shipment.shipment_number }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Shipment details and tracking information</p>
      </div>
      <div class="flex gap-2">
        <button
          v-if="shipment.status === 'pending'"
          @click="handleDispatch"
          class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500"
        >
          Dispatch
        </button>
        <button
          v-if="shipment.status === 'in_transit'"
          @click="handleDeliver"
          class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500"
        >
          Mark Delivered
        </button>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Status</p>
        <StatusBadge :status="shipment.status" type="shipment" />
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Carrier</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ shipment.carrier }}</p>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Tracking Number</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ shipment.tracking_number || 'N/A' }}</p>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Order</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ shipment.sales_order?.order_number }}</p>
      </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Shipping Address</h3>
      <p class="text-gray-600 dark:text-slate-300">
        {{ shipment.shipping_address?.street }}, {{ shipment.shipping_address?.city }}, {{ shipment.shipping_address?.state }} {{ shipment.shipping_address?.postal_code }}
      </p>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Customs Documents</h3>
      <div v-if="shipment.customs_documents.length === 0" class="text-center py-8">
        <p class="text-gray-500 dark:text-slate-400">No customs documents</p>
      </div>
      <div v-else class="space-y-2">
        <div v-for="doc in shipment.customs_documents" :key="doc.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-700 rounded-lg">
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ doc.type }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400">{{ doc.document_number }}</p>
          </div>
          <a :href="doc.path" target="_blank" class="text-sm text-primary-600 hover:text-primary-800">Download</a>
        </div>
      </div>
    </div>
  </div>
</template>
