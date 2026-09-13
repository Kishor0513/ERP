<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useProcurementStore } from '@/stores/procurement'
import Modal from '@/components/Modal.vue'
import DataTable from '@/components/DataTable.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import BulkBar from '@/components/BulkBar.vue'

const procurementStore = useProcurementStore()
const showCreateModal = ref(false)
const editingSupplier = ref<any>(null)
const search = ref('')
const selectedIds = ref<number[]>([])
const filterValues = ref<Record<string, any>>({ status: '', category: '' })

const form = ref({
  name: '',
  contact_name: '',
  email: '',
  phone: '',
  address: {
    street: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
  },
  lead_time_days: 7,
  payment_terms: 30,
  rating: 3,
})

const columns = [
  { key: 'name', label: 'Supplier', sortable: true },
  { key: 'contact_name', label: 'Contact' },
  { key: 'email', label: 'Email' },
  { key: 'phone', label: 'Phone' },
  { key: 'lead_time_days', label: 'Lead Time', format: (v: number) => v != null ? `${v} days` : '-' },
  { key: 'rating', label: 'Rating', format: (v: number) => v != null ? `${v}/5` : '-' },
  { key: 'status', label: 'Status' },
]

const filters = [
  { key: 'status', label: 'Status', type: 'select' as const, options: [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
  ] },
  { key: 'category', label: 'Category', type: 'select' as const, options: [
    { value: 'wool', label: 'Wool' },
    { value: 'dye', label: 'Dye' },
    { value: 'packaging', label: 'Packaging' },
    { value: 'other', label: 'Other' },
  ] },
]

const filteredSuppliers = computed(() => {
  let list = [...procurementStore.suppliers] as any[]
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter((s) => [s.name, s.contact_name, s.email].filter(Boolean).some((v) => String(v).toLowerCase().includes(q)))
  }
  if (filterValues.value.status === 'active') list = list.filter((s) => s.is_active !== false)
  if (filterValues.value.status === 'inactive') list = list.filter((s) => s.is_active === false)
  if (filterValues.value.category) list = list.filter((s) => s.category === filterValues.value.category)
  return list
})

onMounted(async () => {
  await procurementStore.fetchSuppliers()
})

const handleSearch = (query: string) => {
  search.value = query
}

const handleFilterChange = (key: string, value: any) => {
  filterValues.value[key] = value
}

const handleFilterReset = () => {
  filterValues.value = { status: '', category: '' }
  search.value = ''
}

const toggleSelect = (id: number) => {
  const i = selectedIds.value.indexOf(id)
  if (i === -1) selectedIds.value.push(id)
  else selectedIds.value.splice(i, 1)
}

const toggleSelectAll = () => {
  const all = filteredSuppliers.value.map((r: any) => r.id)
  const allSelected = all.length > 0 && all.every((id) => selectedIds.value.includes(id))
  selectedIds.value = allSelected ? [] : all
}

const exportCsv = () => {
  const rows = filteredSuppliers.value.filter((r: any) => selectedIds.value.length === 0 || selectedIds.value.includes(r.id))
  const header = ['name', 'contact_name', 'email', 'phone', 'lead_time_days', 'rating', 'status']
  const lines = [header.join(',')]
  for (const r of rows as any[]) {
    const status = r.is_active === false ? 'inactive' : 'active'
    lines.push([r.name, r.contact_name, r.email, r.phone, r.lead_time_days, r.rating, status].map((v) => `"${String(v ?? '').replace(/"/g, '""')}"`).join(','))
  }
  const blob = new Blob([lines.join('\n')], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'suppliers.csv'
  a.click()
  URL.revokeObjectURL(url)
}

const handleBulkDelete = async () => {
  if (!selectedIds.value.length) return
  if (!confirm(`Delete ${selectedIds.value.length} suppliers?`)) return
  for (const id of [...selectedIds.value]) {
    await procurementStore.deleteSupplier(id)
  }
  selectedIds.value = []
}

const openCreateModal = () => {
  editingSupplier.value = null
  form.value = {
    name: '',
    contact_name: '',
    email: '',
    phone: '',
    address: { street: '', city: '', state: '', postal_code: '', country: '' },
    lead_time_days: 7,
    payment_terms: 30,
    rating: 3,
  }
  showCreateModal.value = true
}

const openEditModal = (supplier: any) => {
  editingSupplier.value = supplier
  form.value = { ...supplier, address: typeof supplier.address === 'string' ? { street: supplier.address, city: '', state: '', postal_code: '', country: '' } : { ...supplier.address } }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  if (editingSupplier.value) {
    await procurementStore.updateSupplier(editingSupplier.value.id, form.value)
  } else {
    await procurementStore.createSupplier(form.value)
  }
  showCreateModal.value = false
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this supplier?')) {
    await procurementStore.deleteSupplier(id)
  }
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Suppliers" subtitle="Manage your supplier contacts and relationships">
      <template #actions>
        <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Add Supplier</button>
      </template>
    </PageHeader>

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="handleFilterReset" />

    <BulkBar :count="selectedIds.length" @export="exportCsv" @delete="handleBulkDelete" @clear="selectedIds = []" />

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="filteredSuppliers"
        :loading="procurementStore.loading"
        :total-items="filteredSuppliers.length"
        :current-page="1"
        :per-page="filteredSuppliers.length || 15"
        selectable
        :selected-ids="selectedIds"
        @search="handleSearch"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-status="{ row }">
          <StatusBadge :status="row.is_active === false ? 'inactive' : 'active'" />
        </template>
        <template #cell-name="{ row }">
          <div class="flex items-center gap-2">
            <span class="font-medium">{{ row.name }}</span>
            <button @click="openEditModal(row)" class="text-xs text-primary-600 hover:text-primary-800">Edit</button>
            <button @click="handleDelete(row.id)" class="text-xs text-red-600 hover:text-red-800">Delete</button>
          </div>
        </template>
      </DataTable>
    </div>

    <Modal :open="showCreateModal" :title="editingSupplier ? 'Edit Supplier' : 'Create Supplier'" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Company Name</label>
          <input v-model="form.name" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Contact Name</label>
          <input v-model="form.contact_name" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Email</label>
            <input v-model="form.email" type="email" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Phone</label>
            <input v-model="form.phone" type="tel" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Street Address</label>
          <input v-model="form.address.street" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
        </div>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">City</label>
            <input v-model="form.address.city" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">State</label>
            <input v-model="form.address.state" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Postal Code</label>
            <input v-model="form.address.postal_code" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Lead Time (days)</label>
            <input v-model.number="form.lead_time_days" type="number" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Payment Terms (days)</label>
            <input v-model.number="form.payment_terms" type="number" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Rating (1-5)</label>
            <input v-model.number="form.rating" type="number" min="1" max="5" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">{{ editingSupplier ? 'Update' : 'Create' }}</button>
      </template>
    </Modal>
  </div>
</template>
