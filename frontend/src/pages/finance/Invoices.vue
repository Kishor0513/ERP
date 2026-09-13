<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useFinanceStore } from '@/stores/finance'
import { useSalesStore } from '@/stores/sales'
import DataTable from '@/components/DataTable.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import BulkBar from '@/components/BulkBar.vue'
import StatStrip from '@/components/StatStrip.vue'

const financeStore = useFinanceStore()
const salesStore = useSalesStore()

const search = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const showPaymentModal = ref(false)
const showCreateModal = ref(false)
const selectedInvoice = ref<any>(null)
const selectedIds = ref<number[]>([])
const filterValues = ref<Record<string, any>>({ status: '' })

const paymentForm = ref({
  amount: 0,
  method: 'bank_transfer',
  reference: '',
})

const invoiceForm = ref({
  sales_order_id: null as number | null,
  due_date: '',
  notes: '',
})

const columns = [
  { key: 'invoice_number', label: 'Invoice #', sortable: true },
  { key: 'sales_order', label: 'Order', format: (v: any) => v?.order_number || '-' },
  { key: 'total', label: 'Total', format: (v: number) => `$${Number(v).toFixed(2)}` },
  { key: 'status', label: 'Status' },
  { key: 'due_date', label: 'Due Date', format: (v: string) => v ? new Date(v).toLocaleDateString() : '-' },
]

const filters = [
  { key: 'status', label: 'Status', type: 'select' as const, options: [
    { value: 'draft', label: 'Draft' },
    { value: 'sent', label: 'Sent' },
    { value: 'partial', label: 'Partial' },
    { value: 'paid', label: 'Paid' },
    { value: 'overdue', label: 'Overdue' },
    { value: 'cancelled', label: 'Cancelled' },
  ] },
]

const stats = computed(() => {
  const list = financeStore.invoices
  const totalBilled = list.reduce((s: number, r: any) => s + Number(r.total || 0), 0)
  const paidTotal = list.filter((r: any) => r.status === 'paid').reduce((s: number, r: any) => s + Number(r.total || 0), 0)
  const overdueCount = list.filter((r: any) => r.status === 'overdue').length
  const paidCount = list.filter((r: any) => r.status === 'paid').length
  return [
    { label: 'Total Billed', value: `$${totalBilled.toLocaleString(undefined, { minimumFractionDigits: 2 })}`, hint: `${list.length} invoices` },
    { label: 'Paid', value: `$${paidTotal.toLocaleString(undefined, { minimumFractionDigits: 2 })}`, hint: `${paidCount} paid` },
    { label: 'Overdue', value: overdueCount, hint: 'need follow-up' },
  ]
})

onMounted(async () => {
  await loadData()
  await salesStore.fetchSalesOrders({ per_page: 100 })
})

const loadData = async () => {
  await financeStore.fetchInvoices({
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
  const all = financeStore.invoices.map((r: any) => r.id)
  const allSelected = all.length > 0 && all.every((id) => selectedIds.value.includes(id))
  selectedIds.value = allSelected ? [] : all
}

const exportCsv = () => {
  const rows = financeStore.invoices.filter((r: any) => selectedIds.value.length === 0 || selectedIds.value.includes(r.id))
  const header = ['invoice_number', 'order', 'total', 'status', 'due_date']
  const lines = [header.join(',')]
  for (const r of rows as any[]) {
    lines.push([r.invoice_number, r.sales_order?.order_number || '', r.total, r.status, r.due_date || ''].map((v) => `"${String(v ?? '').replace(/"/g, '""')}"`).join(','))
  }
  const blob = new Blob([lines.join('\n')], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'invoices.csv'
  a.click()
  URL.revokeObjectURL(url)
}

const openCreateModal = () => {
  invoiceForm.value = { sales_order_id: null, due_date: '', notes: '' }
  showCreateModal.value = true
}

const handleCreateInvoice = async () => {
  await financeStore.createInvoice({
    sales_order_id: invoiceForm.value.sales_order_id!,
    due_date: invoiceForm.value.due_date,
    notes: invoiceForm.value.notes,
  } as any)
  showCreateModal.value = false
  await loadData()
}

const openPaymentModal = (invoice: any) => {
  selectedInvoice.value = invoice
  paymentForm.value = {
    amount: invoice.total,
    method: 'bank_transfer',
    reference: '',
  }
  showPaymentModal.value = true
}

const handlePayment = async () => {
  await financeStore.recordPayment(selectedInvoice.value.id, paymentForm.value)
  showPaymentModal.value = false
  loadData()
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Invoices" subtitle="Generate invoices and track customer payments">
      <template #actions>
        <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Create Invoice</button>
      </template>
    </PageHeader>

    <StatStrip :stats="stats" :loading="financeStore.loading" />

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="handleFilterReset" />

    <BulkBar :count="selectedIds.length" @export="exportCsv" @clear="selectedIds = []" />

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="financeStore.invoices"
        :loading="financeStore.loading"
        :total-items="financeStore.totalInvoices"
        :current-page="currentPage"
        :per-page="perPage"
        selectable
        :selected-ids="selectedIds"
        @search="handleSearch"
        @update:current-page="handlePageChange"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
        <template #cell-invoice_number="{ row }">
          <div class="flex items-center gap-2">
            <span>{{ row.invoice_number }}</span>
            <button
              v-if="row.status === 'sent' || row.status === 'overdue'"
              @click="openPaymentModal(row)"
              class="text-xs text-primary-600 hover:text-primary-800"
            >
              Record Payment
            </button>
          </div>
        </template>
      </DataTable>
    </div>

    <Modal :open="showPaymentModal" title="Record Payment" @close="showPaymentModal = false">
      <form @submit.prevent="handlePayment" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Amount</label>
          <input v-model.number="paymentForm.amount" type="number" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Payment Method</label>
          <select v-model="paymentForm.method" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm">
            <option value="cash">Cash</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="credit_card">Credit Card</option>
            <option value="cheque">Cheque</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Reference</label>
          <input v-model="paymentForm.reference" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
        </div>
      </form>
      <template #footer>
        <button @click="showPaymentModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handlePayment" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Record Payment</button>
      </template>
    </Modal>

    <Modal :open="showCreateModal" title="Create Invoice" @close="showCreateModal = false">
      <form @submit.prevent="handleCreateInvoice" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Sales Order</label>
          <select v-model="invoiceForm.sales_order_id" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required>
            <option :value="null" disabled>Select order</option>
            <option v-for="o in salesStore.salesOrders" :key="o.id" :value="o.id">{{ o.order_number }} - {{ o.customer_name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Due Date</label>
          <input v-model="invoiceForm.due_date" type="date" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Notes</label>
          <textarea v-model="invoiceForm.notes" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" rows="2"></textarea>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleCreateInvoice" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Create</button>
      </template>
    </Modal>
  </div>
</template>
