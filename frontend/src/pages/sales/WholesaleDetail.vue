<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSalesStore } from '@/stores/sales'
import StatusBadge from '@/components/StatusBadge.vue'

const route = useRoute()
const router = useRouter()
const salesStore = useSalesStore()

const account = ref<any>(null)

onMounted(async () => {
  const id = Number(route.params.id)
  account.value = await salesStore.fetchWholesaleAccount(id)
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(value)
}

const handleApprove = async () => {
  await salesStore.approveAccount(account.value.id)
  account.value = await salesStore.fetchWholesaleAccount(account.value.id)
}

const handleReject = async () => {
  if (confirm('Are you sure you want to reject this account?')) {
    await salesStore.rejectAccount(account.value.id)
    account.value = await salesStore.fetchWholesaleAccount(account.value.id)
  }
}
</script>

<template>
  <div class="space-y-6" v-if="account">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <button
          @click="router.back()"
          class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mb-2"
        >
          ← Back to Accounts
        </button>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ account.company_name }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ account.contact_name }}</p>
      </div>
      <div class="flex gap-2">
        <button
          v-if="account.status === 'pending'"
          @click="handleApprove"
          class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500"
        >
          Approve
        </button>
        <button
          v-if="account.status === 'pending'"
          @click="handleReject"
          class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50"
        >
          Reject
        </button>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Status</p>
        <StatusBadge :status="account.status" />
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Credit Limit</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatCurrency(account.credit_limit) }}</p>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Payment Terms</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ account.payment_terms }} days</p>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Discount</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ account.discount_percentage }}%</p>
      </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Contact Information</h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-gray-500 dark:text-slate-400">Email</p>
          <p class="text-gray-900 dark:text-white">{{ account.email }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500 dark:text-slate-400">Phone</p>
          <p class="text-gray-900 dark:text-white">{{ account.phone }}</p>
        </div>
        <div class="col-span-2">
          <p class="text-sm text-gray-500 dark:text-slate-400">Address</p>
          <p class="text-gray-900 dark:text-white">
            {{ account.address?.street }}, {{ account.address?.city }}, {{ account.address?.state }} {{ account.address?.postal_code }}
          </p>
        </div>
      </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Documents</h3>
      <div v-if="account.documents.length === 0" class="text-center py-8">
        <p class="text-gray-500 dark:text-slate-400">No documents uploaded</p>
      </div>
      <div v-else class="space-y-2">
        <div v-for="doc in account.documents" :key="doc.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-700 rounded-lg">
          <span class="text-sm text-gray-900 dark:text-white">{{ doc.name }}</span>
          <a :href="doc.path" target="_blank" class="text-sm text-primary-600 hover:text-primary-800">Download</a>
        </div>
      </div>
    </div>
  </div>
</template>
