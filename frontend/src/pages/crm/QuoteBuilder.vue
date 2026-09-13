<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCrmStore } from '@/stores/crm'
import StatusBadge from '@/components/StatusBadge.vue'

const route = useRoute()
const router = useRouter()
const crmStore = useCrmStore()

const quote = ref<any>(null)
const showAddItemModal = ref(false)

onMounted(async () => {
  const id = Number(route.params.id)
  quote.value = await crmStore.fetchQuote(id)
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(value)
}

const handleSend = async () => {
  await crmStore.sendQuote(quote.value.id)
  quote.value = await crmStore.fetchQuote(quote.value.id)
}

const handleAccept = async () => {
  if (confirm('Accept this quote?')) {
    await crmStore.acceptQuote(quote.value.id)
    quote.value = await crmStore.fetchQuote(quote.value.id)
  }
}

const handleConvertToOrder = async () => {
  if (confirm('Convert this quote to a sales order?')) {
    await crmStore.convertToOrder(quote.value.id)
    router.push('/sales/orders')
  }
}
</script>

<template>
  <div class="space-y-6" v-if="quote">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <button
          @click="router.back()"
          class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mb-2"
        >
          ← Back to Quotes
        </button>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ quote.quote_number }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ quote.customer_name }}</p>
      </div>
      <div class="flex gap-2">
        <button
          v-if="quote.status === 'draft'"
          @click="handleSend"
          class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500"
        >
          Send Quote
        </button>
        <button
          v-if="quote.status === 'sent'"
          @click="handleAccept"
          class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500"
        >
          Accept Quote
        </button>
        <button
          v-if="quote.status === 'accepted'"
          @click="handleConvertToOrder"
          class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500"
        >
          Convert to Order
        </button>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Status</p>
        <StatusBadge :status="quote.status" />
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Total</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(quote.total) }}</p>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Valid Until</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ new Date(quote.valid_until).toLocaleDateString() }}</p>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Created</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ new Date(quote.created_at).toLocaleDateString() }}</p>
      </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <div class="p-4 border-b border-gray-200 dark:border-slate-700 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Line Items</h3>
        <button @click="showAddItemModal = true" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Add Item</button>
      </div>
      <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
        <thead class="bg-gray-50 dark:bg-slate-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Description</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Unit Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Total</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
          <tr v-for="item in quote.items" :key="item.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ item.product?.name || 'Custom' }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ item.description }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ item.quantity }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatCurrency(item.unit_price) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ formatCurrency(item.total) }}</td>
          </tr>
        </tbody>
      </table>
      <div class="p-4 border-t border-gray-200 dark:border-slate-700">
        <div class="flex justify-end space-y-2">
          <div class="text-right">
            <p class="text-sm text-gray-500 dark:text-slate-400">Subtotal: {{ formatCurrency(quote.subtotal) }}</p>
            <p class="text-sm text-gray-500 dark:text-slate-400">Tax: {{ formatCurrency(quote.tax) }}</p>
            <p class="text-sm text-gray-500 dark:text-slate-400">Discount: {{ formatCurrency(quote.discount) }}</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">Total: {{ formatCurrency(quote.total) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
