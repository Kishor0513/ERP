<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useInventoryStore } from '@/stores/inventory'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import DataTable from '@/components/DataTable.vue'
import BulkBar from '@/components/BulkBar.vue'
import Modal from '@/components/Modal.vue'
import { useList } from '@/composables/useList'

const inventoryStore = useInventoryStore()
const showCreateModal = ref(false)
const editingWarehouse = ref<any>(null)

const form = ref({
  name: '',
  code: '',
  address: '',
  city: '',
  state: '',
  country: '',
})

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'code', label: 'Code', sortable: true },
  { key: 'city', label: 'City' },
  { key: 'state', label: 'State' },
  { key: 'country', label: 'Country' },
  { key: 'actions', label: 'Actions' },
]

const warehouseFilters = [
  { key: 'search', label: 'Search', type: 'text' as const, placeholder: 'Name or code...' },
  { key: 'city', label: 'City', type: 'text' as const, placeholder: 'Filter by city...' },
]

const { currentPage, perPage, filters, selected, load, setFilter, toggleSelect, clearSelection, exportCsv } = useList(async () => {
  await inventoryStore.fetchWarehouses()
})

onMounted(async () => {
  await load()
})

const filteredWarehouses = computed(() => {
  const q = (filters.value.search || '').toLowerCase()
  const c = (filters.value.city || '').toLowerCase()
  return inventoryStore.warehouses.filter((w: any) => {
    const matchQ = !q || [w.name, w.code, w.city].some((v) => String(v || '').toLowerCase().includes(q))
    const matchC = !c || String(w.city || '').toLowerCase().includes(c)
    return matchQ && matchC
  })
})

const pagedWarehouses = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredWarehouses.value.slice(start, start + perPage.value)
})

const handleReset = () => {
  Object.keys(filters.value).forEach((k) => delete filters.value[k])
  currentPage.value = 1
  load()
}

const handlePageChange = (p: number) => {
  currentPage.value = p
}

const toggleSelectAll = () => {
  const ids = pagedWarehouses.value.map((w: any) => w.id)
  const all = ids.length > 0 && ids.every((id) => selected.value.includes(id))
  if (all) clearSelection()
  else selected.value = [...new Set([...selected.value, ...ids])]
}

const flatRows = (rows: any[]) => rows.map((w: any) => ({
  name: w.name,
  code: w.code,
  address: w.address || '',
  city: w.city || '',
  state: w.state || '',
  country: w.country || '',
}))

const exportSelected = () => {
  exportCsv(flatRows(filteredWarehouses.value.filter((w: any) => selected.value.includes(w.id))), 'warehouses')
}

const exportAll = () => {
  exportCsv(flatRows(filteredWarehouses.value), 'warehouses')
}

const handleBulkDelete = async () => {
  if (!confirm(`Are you sure you want to delete ${selected.value.length} warehouses?`)) return
  for (const id of selected.value) {
    await inventoryStore.deleteWarehouse(id)
  }
  clearSelection()
  load()
}

const openCreateModal = () => {
  editingWarehouse.value = null
  form.value = { name: '', code: '', address: '', city: '', state: '', country: '' }
  showCreateModal.value = true
}

const openEditModal = (warehouse: any) => {
  editingWarehouse.value = warehouse
  form.value = { ...warehouse }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  if (editingWarehouse.value) {
    await inventoryStore.updateWarehouse(editingWarehouse.value.id, form.value)
  } else {
    await inventoryStore.createWarehouse(form.value)
  }
  showCreateModal.value = false
  load()
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this warehouse?')) {
    await inventoryStore.deleteWarehouse(id)
    load()
  }
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Warehouses" subtitle="Manage your warehouse locations and storage facilities">
      <template #actions>
        <button @click="exportAll" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Export</button>
        <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Add Warehouse</button>
      </template>
    </PageHeader>

    <FilterBar :filters="warehouseFilters" :values="filters" @change="setFilter" @reset="handleReset" />

    <BulkBar :count="selected.length" @export="exportSelected" @delete="handleBulkDelete" @clear="clearSelection" />

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="pagedWarehouses"
        :loading="inventoryStore.loading"
        :total-items="filteredWarehouses.length"
        :current-page="currentPage"
        :per-page="perPage"
        :searchable="false"
        selectable
        :selected-ids="selected"
        @update:current-page="handlePageChange"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-actions="{ row }">
          <div class="flex gap-2">
            <button @click="openEditModal(row)" class="text-sm text-primary-600 hover:text-primary-800">Edit</button>
            <button @click="handleDelete(row.id)" class="text-sm text-red-600 hover:text-red-800">Delete</button>
          </div>
        </template>
      </DataTable>
    </div>

    <Modal :open="showCreateModal" :title="editingWarehouse ? 'Edit Warehouse' : 'Create Warehouse'" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Name</label>
          <input v-model="form.name" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Code</label>
          <input v-model="form.code" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Address</label>
          <input v-model="form.address" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">City</label>
            <input v-model="form.city" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">State</label>
            <input v-model="form.state" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Country</label>
          <input v-model="form.country" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">{{ editingWarehouse ? 'Update' : 'Create' }}</button>
      </template>
    </Modal>
  </div>
</template>
