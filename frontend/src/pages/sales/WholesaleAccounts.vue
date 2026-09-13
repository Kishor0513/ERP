<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSalesStore } from '@/stores/sales'
import DataTable from '@/components/DataTable.vue'
import StatusBadge from '@/components/StatusBadge.vue'

const router = useRouter()
const salesStore = useSalesStore()

const search = ref('')
const currentPage = ref(1)
const perPage = ref(15)

const columns = [
  { key: 'company_name', label: 'Company', sortable: true },
  { key: 'contact_name', label: 'Contact', sortable: true },
  { key: 'email', label: 'Email' },
  { key: 'credit_limit', label: 'Credit Limit', format: (v: number) => `$${v.toFixed(2)}` },
  { key: 'status', label: 'Status' },
]

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  await salesStore.fetchWholesaleAccounts({
    page: currentPage.value,
    per_page: perPage.value,
    search: search.value,
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

const viewAccount = (id: number) => {
  router.push(`/sales/wholesale/${id}`)
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Wholesale Accounts</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Manage your wholesale customer accounts and credit terms</p>
      </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="salesStore.wholesaleAccounts"
        :loading="salesStore.loading"
        :total-items="salesStore.totalAccounts"
        :current-page="currentPage"
        :per-page="perPage"
        @search="handleSearch"
        @update:current-page="handlePageChange"
      >
        <template #cell-company_name="{ row }">
          <button
            @click="viewAccount(row.id)"
            class="text-primary-600 hover:text-primary-800 font-medium"
          >
            {{ row.company_name }}
          </button>
        </template>
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
      </DataTable>
    </div>
  </div>
</template>
