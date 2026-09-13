<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useFinanceStore } from '@/stores/finance'
import DataTable from '@/components/DataTable.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import BulkBar from '@/components/BulkBar.vue'

const financeStore = useFinanceStore()

const search = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const showCreateModal = ref(false)
const editingExpense = ref<any>(null)
const selectedIds = ref<number[]>([])
const filterValues = ref<Record<string, any>>({ category: '' })

const form = ref({
  category: '',
  description: '',
  amount: 0,
  date: new Date().toISOString().split('T')[0],
})

const categories = ['Materials', 'Labor', 'Utilities', 'Rent', 'Marketing', 'Office', 'Travel', 'Other']

const columns = [
  { key: 'date', label: 'Date', sortable: true, format: (v: string) => v ? new Date(v).toLocaleDateString() : '-' },
  { key: 'category', label: 'Category', sortable: true },
  { key: 'description', label: 'Description' },
  { key: 'amount', label: 'Amount', format: (v: number) => `$${Number(v).toFixed(2)}` },
  { key: 'status', label: 'Status' },
]

const filters = [
  { key: 'category', label: 'Category', type: 'select' as const, options: [
    { value: 'raw_materials', label: 'Raw Materials' },
    { value: 'payroll', label: 'Payroll' },
    { value: 'shipping', label: 'Shipping' },
    { value: 'overhead', label: 'Overhead' },
    { value: 'marketing', label: 'Marketing' },
    { value: 'other', label: 'Other' },
  ] },
]

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  await financeStore.fetchExpenses({
    page: currentPage.value,
    per_page: perPage.value,
    search: search.value,
    category: filterValues.value.category || undefined,
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
  filterValues.value = { category: '' }
  currentPage.value = 1
  loadData()
}

const toggleSelect = (id: number) => {
  const i = selectedIds.value.indexOf(id)
  if (i === -1) selectedIds.value.push(id)
  else selectedIds.value.splice(i, 1)
}

const toggleSelectAll = () => {
  const all = financeStore.expenses.map((r: any) => r.id)
  const allSelected = all.length > 0 && all.every((id) => selectedIds.value.includes(id))
  selectedIds.value = allSelected ? [] : all
}

const exportCsv = () => {
  const rows = financeStore.expenses.filter((r: any) => selectedIds.value.length === 0 || selectedIds.value.includes(r.id))
  const header = ['date', 'category', 'description', 'amount', 'status']
  const lines = [header.join(',')]
  for (const r of rows as any[]) {
    lines.push([r.date || r.incurred_at || '', r.category, r.description, r.amount, r.status || ''].map((v) => `"${String(v ?? '').replace(/"/g, '""')}"`).join(','))
  }
  const blob = new Blob([lines.join('\n')], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'expenses.csv'
  a.click()
  URL.revokeObjectURL(url)
}

const handleBulkDelete = async () => {
  if (!selectedIds.value.length) return
  if (!confirm(`Delete ${selectedIds.value.length} expenses?`)) return
  for (const id of [...selectedIds.value]) {
    await financeStore.deleteExpense(id)
  }
  selectedIds.value = []
  loadData()
}

const openCreateModal = () => {
  editingExpense.value = null
  form.value = { category: '', description: '', amount: 0, date: new Date().toISOString().split('T')[0] }
  showCreateModal.value = true
}

const openEditModal = (expense: any) => {
  editingExpense.value = expense
  form.value = { ...expense }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  if (editingExpense.value) {
    await financeStore.updateExpense(editingExpense.value.id, form.value)
  } else {
    await financeStore.createExpense(form.value)
  }
  showCreateModal.value = false
  loadData()
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this expense?')) {
    await financeStore.deleteExpense(id)
    loadData()
  }
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Expenses" subtitle="Track and categorize business expenses">
      <template #actions>
        <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Add Expense</button>
      </template>
    </PageHeader>

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="handleFilterReset" />

    <BulkBar :count="selectedIds.length" @export="exportCsv" @delete="handleBulkDelete" @clear="selectedIds = []" />

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="financeStore.expenses"
        :loading="financeStore.loading"
        :total-items="financeStore.totalExpenses"
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
          <StatusBadge :status="row.status || 'pending'" type="payment" />
        </template>
        <template #cell-description="{ row }">
          <div class="flex items-center gap-2">
            <span>{{ row.description }}</span>
            <button @click="openEditModal(row)" class="text-xs text-primary-600 hover:text-primary-800">Edit</button>
            <button @click="handleDelete(row.id)" class="text-xs text-red-600 hover:text-red-800">Delete</button>
          </div>
        </template>
      </DataTable>
    </div>

    <Modal :open="showCreateModal" :title="editingExpense ? 'Edit Expense' : 'Add Expense'" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Category</label>
          <select v-model="form.category" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required>
            <option value="">Select category</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Description</label>
          <input v-model="form.description" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Amount</label>
            <input v-model.number="form.amount" type="number" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Date</label>
            <input v-model="form.date" type="date" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
          </div>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">{{ editingExpense ? 'Update' : 'Create' }}</button>
      </template>
    </Modal>
  </div>
</template>
