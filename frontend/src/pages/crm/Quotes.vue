<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCrmStore } from '@/stores/crm'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import DataTable from '@/components/DataTable.vue'
import BulkBar from '@/components/BulkBar.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'

const router = useRouter()
const crmStore = useCrmStore()

const search = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const statusFilter = ref('')
const selectedIds = ref<number[]>([])
const showCreateModal = ref(false)
const creating = ref(false)
const newQuote = ref({ customer_name: '', customer_email: '', valid_until: '' })

const columns = [
  { key: 'quote_number', label: 'Quote #', sortable: true },
  { key: 'customer_name', label: 'Customer', sortable: true },
  { key: 'total', label: 'Total', format: (v: number) => `$${Number(v || 0).toFixed(2)}` },
  { key: 'status', label: 'Status' },
  { key: 'valid_until', label: 'Valid Until', format: (v: string) => v ? new Date(v).toLocaleDateString() : '-' },
  { key: 'created_at', label: 'Created', format: (v: string) => new Date(v).toLocaleDateString() },
]

const filters = [
  { key: 'status', label: 'Status', type: 'select' as const, options: [
    { value: 'draft', label: 'Draft' },
    { value: 'sent', label: 'Sent' },
    { value: 'accepted', label: 'Accepted' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'expired', label: 'Expired' },
  ]},
]

const filterValues = computed(() => ({ status: statusFilter.value }))

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  await crmStore.fetchQuotes({
    page: currentPage.value,
    per_page: perPage.value,
    search: search.value,
    status: statusFilter.value || undefined,
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
  if (key === 'status') statusFilter.value = value
  currentPage.value = 1
  loadData()
}

const resetFilters = () => {
  statusFilter.value = ''
  currentPage.value = 1
  loadData()
}

const toggleSelect = (id: number) => {
  const i = selectedIds.value.indexOf(id)
  if (i >= 0) selectedIds.value.splice(i, 1)
  else selectedIds.value.push(id)
}

const toggleSelectAll = () => {
  const all = crmStore.quotes.map((q: any) => q.id)
  if (all.every((id: number) => selectedIds.value.includes(id))) selectedIds.value = []
  else selectedIds.value = all
}

const exportCsv = () => {
  const rows = crmStore.quotes.filter((q: any) => selectedIds.value.includes(q.id))
  const data = rows.length ? rows : crmStore.quotes
  if (!data.length) return
  const headers = ['id', 'quote_number', 'customer_name', 'total', 'status', 'valid_until']
  const esc = (v: any) => `"${String(v ?? '').replace(/"/g, '""')}"`
  const csv = [headers.join(','), ...data.map((r: any) => headers.map(h => esc(r[h])).join(','))].join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `quotes-${new Date().toISOString().slice(0, 10)}.csv`
  a.click()
  URL.revokeObjectURL(url)
}

const viewQuote = (id: number) => {
  router.push(`/crm/quotes/${id}`)
}

const createQuote = async () => {
  if (!newQuote.value.customer_name.trim()) return
  creating.value = true
  try {
    const quote: any = await crmStore.createQuote({
      customer_name: newQuote.value.customer_name.trim(),
      customer_email: newQuote.value.customer_email.trim() || undefined,
      valid_until: newQuote.value.valid_until || undefined,
    } as any)
    showCreateModal.value = false
    newQuote.value = { customer_name: '', customer_email: '', valid_until: '' }
    if (quote?.id) router.push(`/crm/quotes/${quote.id}`)
    else loadData()
  } finally {
    creating.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Quotes" subtitle="Create and manage customer quotations">
      <template #actions>
        <button @click="showCreateModal = true" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Create Quote</button>
      </template>
    </PageHeader>

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="resetFilters" />

    <BulkBar :count="selectedIds.length" @export="exportCsv" @clear="selectedIds = []" />

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="crmStore.quotes"
        :loading="crmStore.loading"
        :total-items="crmStore.totalQuotes"
        :current-page="currentPage"
        :per-page="perPage"
        selectable
        :selected-ids="selectedIds"
        @search="handleSearch"
        @update:current-page="handlePageChange"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-quote_number="{ row }">
          <button
            @click="viewQuote(row.id)"
            class="text-primary-600 hover:text-primary-800 font-medium"
          >
            {{ row.quote_number }}
          </button>
        </template>
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
      </DataTable>
    </div>

    <Modal :open="showCreateModal" title="New Quote" @close="showCreateModal = false">
      <div class="space-y-4">
        <div>
          <label class="label">Customer name</label>
          <input v-model="newQuote.customer_name" class="input" placeholder="e.g. Acme Trading" />
        </div>
        <div>
          <label class="label">Customer email</label>
          <input v-model="newQuote.customer_email" type="email" class="input" placeholder="buyer@company.com" />
        </div>
        <div>
          <label class="label">Valid until</label>
          <input v-model="newQuote.valid_until" type="date" class="input" />
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button @click="showCreateModal = false" class="btn-secondary">Cancel</button>
          <button @click="createQuote" :disabled="creating || !newQuote.customer_name.trim()" class="btn-primary">Create & Open</button>
        </div>
      </div>
    </Modal>
  </div>
</template>
