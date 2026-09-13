<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useInventoryStore } from '@/stores/inventory'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import DataTable from '@/components/DataTable.vue'
import BulkBar from '@/components/BulkBar.vue'
import StatStrip from '@/components/StatStrip.vue'
import Modal from '@/components/Modal.vue'
import { useList } from '@/composables/useList'

const inventoryStore = useInventoryStore()

const showCreateModal = ref(false)
const showAdjustModal = ref(false)
const editingMaterial = ref<any>(null)
const selectedMaterial = ref<any>(null)

const form = ref<{
  name: string
  sku: string
  unit: string
  cost_per_unit: number
  stock_quantity: number
  reorder_point: number
  supplier_id: number | null
}>({
  name: '',
  sku: '',
  unit: 'pcs',
  cost_per_unit: 0,
  stock_quantity: 0,
  reorder_point: 10,
  supplier_id: null,
})

const adjustmentForm = ref({
  type: 'in',
  quantity: 0,
  notes: '',
})

const columns = [
  { key: 'sku', label: 'SKU', sortable: true },
  { key: 'name', label: 'Name', sortable: true },
  { key: 'unit', label: 'Unit' },
  { key: 'stock_quantity', label: 'Stock', sortable: true },
  { key: 'reorder_point', label: 'Reorder Point' },
  { key: 'cost_per_unit', label: 'Cost / Unit' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' },
]

const stockFilters = [
  {
    key: 'stock',
    label: 'Stock Status',
    type: 'select' as const,
    options: [
      { value: 'low', label: 'Low Stock' },
      { value: 'in_stock', label: 'In Stock' },
    ],
  },
]

const { currentPage, perPage, filters, selected, load, onSearch, onPage, setFilter, toggleSelect, clearSelection, exportCsv } = useList(async (p) => {
  const q = p as unknown as Record<string, any>
  await inventoryStore.fetchRawMaterials({
    page: q.page,
    per_page: q.per_page,
    search: q.search || undefined,
  })
})

onMounted(async () => {
  await load()
  await inventoryStore.fetchLowStock()
})

const isLow = (row: any) => row.stock_quantity <= row.reorder_point

const filteredMaterials = computed(() => {
  if (filters.value.stock === 'low') return inventoryStore.rawMaterials.filter(isLow)
  if (filters.value.stock === 'in_stock') return inventoryStore.rawMaterials.filter((m: any) => !isLow(m))
  return inventoryStore.rawMaterials
})

const totalValue = computed(() =>
  inventoryStore.rawMaterials.reduce((s: number, m: any) => s + (Number(m.stock_quantity) || 0) * (Number(m.cost_per_unit) || 0), 0)
)

const stats = computed(() => [
  { label: 'Total Items', value: inventoryStore.totalRawMaterials },
  { label: 'Low Stock', value: inventoryStore.lowStockItems.length, hint: 'below reorder point' },
  { label: 'Total Value', value: `$${totalValue.value.toLocaleString(undefined, { maximumFractionDigits: 2 })}`, hint: 'stock × unit cost' },
])

const handleReset = () => {
  Object.keys(filters.value).forEach((k) => delete filters.value[k])
  currentPage.value = 1
  load()
}

const toggleSelectAll = () => {
  const ids = filteredMaterials.value.map((m: any) => m.id)
  const all = ids.length > 0 && ids.every((id) => selected.value.includes(id))
  if (all) clearSelection()
  else selected.value = [...new Set([...selected.value, ...ids])]
}

const flatRows = (rows: any[]) => rows.map((m: any) => ({
  sku: m.sku,
  name: m.name,
  unit: m.unit,
  stock_quantity: m.stock_quantity,
  reorder_point: m.reorder_point,
  cost_per_unit: m.cost_per_unit,
  status: isLow(m) ? 'Low Stock' : 'In Stock',
}))

const exportSelected = () => {
  exportCsv(flatRows(filteredMaterials.value.filter((m: any) => selected.value.includes(m.id))), 'raw-materials')
}

const exportAll = () => {
  exportCsv(flatRows(filteredMaterials.value), 'raw-materials')
}

const handleBulkDelete = async () => {
  if (!confirm(`Are you sure you want to delete ${selected.value.length} materials?`)) return
  for (const id of selected.value) {
    await inventoryStore.deleteRawMaterial(id)
  }
  clearSelection()
  load()
}

const openCreateModal = () => {
  editingMaterial.value = null
  form.value = { name: '', sku: '', unit: 'pcs', cost_per_unit: 0, stock_quantity: 0, reorder_point: 10, supplier_id: null }
  showCreateModal.value = true
}

const openEditModal = (material: any) => {
  editingMaterial.value = material
  form.value = { ...material }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  if (editingMaterial.value) {
    await inventoryStore.updateRawMaterial(editingMaterial.value.id, form.value)
  } else {
    await inventoryStore.createRawMaterial(form.value)
  }
  showCreateModal.value = false
  load()
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this material?')) {
    await inventoryStore.deleteRawMaterial(id)
    load()
  }
}

const openAdjustModal = (material: any) => {
  selectedMaterial.value = material
  adjustmentForm.value = { type: 'in', quantity: 0, notes: '' }
  showAdjustModal.value = true
}

const handleAdjust = async () => {
  await inventoryStore.createStockMovement({
    raw_material_id: selectedMaterial.value.id,
    type: adjustmentForm.value.type as any,
    quantity: adjustmentForm.value.quantity,
    notes: adjustmentForm.value.notes,
  })
  showAdjustModal.value = false
  load()
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Raw Materials" subtitle="Track and manage your raw materials inventory">
      <template #actions>
        <button @click="exportAll" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Export</button>
        <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Add Material</button>
      </template>
    </PageHeader>

    <StatStrip :stats="stats" :loading="inventoryStore.loading" />

    <FilterBar :filters="stockFilters" :values="filters" @change="setFilter" @reset="handleReset" />

    <BulkBar :count="selected.length" @export="exportSelected" @delete="handleBulkDelete" @clear="clearSelection" />

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="filteredMaterials"
        :loading="inventoryStore.loading"
        :total-items="inventoryStore.totalRawMaterials"
        :current-page="currentPage"
        :per-page="perPage"
        selectable
        :selected-ids="selected"
        @search="onSearch"
        @update:current-page="onPage"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-stock_quantity="{ row }">
          <span :class="isLow(row) ? 'text-red-600 font-bold' : 'text-gray-900 dark:text-white'" class="font-medium">
            {{ row.stock_quantity }}
          </span>
        </template>
        <template #cell-status="{ row }">
          <span
            :class="isLow(row) ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'"
            class="px-2 py-1 rounded-full text-xs font-medium"
          >
            {{ isLow(row) ? 'Low Stock' : 'In Stock' }}
          </span>
        </template>
        <template #cell-actions="{ row }">
          <div class="flex gap-2">
            <button @click="openAdjustModal(row)" class="text-sm text-primary-600 hover:text-primary-800">Adjust</button>
            <button @click="openEditModal(row)" class="text-sm text-gray-600 dark:text-slate-300 hover:text-gray-900">Edit</button>
            <button @click="handleDelete(row.id)" class="text-sm text-red-600 hover:text-red-800">Delete</button>
          </div>
        </template>
      </DataTable>
    </div>

    <Modal :open="showCreateModal" :title="editingMaterial ? 'Edit Material' : 'Create Material'" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Name</label>
          <input v-model="form.name" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">SKU</label>
            <input v-model="form.sku" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Unit</label>
            <select v-model="form.unit" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm">
              <option value="pcs">Pieces</option>
              <option value="kg">Kilograms</option>
              <option value="m">Meters</option>
              <option value="l">Liters</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Cost per Unit</label>
            <input v-model.number="form.cost_per_unit" type="number" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Stock Quantity</label>
            <input v-model.number="form.stock_quantity" type="number" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Reorder Point</label>
          <input v-model.number="form.reorder_point" type="number" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">{{ editingMaterial ? 'Update' : 'Create' }}</button>
      </template>
    </Modal>

    <Modal :open="showAdjustModal" title="Adjust Stock" @close="showAdjustModal = false">
      <form @submit.prevent="handleAdjust" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Type</label>
          <select v-model="adjustmentForm.type" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm">
            <option value="in">Stock In</option>
            <option value="out">Stock Out</option>
            <option value="adjustment">Adjustment</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Quantity</label>
          <input v-model.number="adjustmentForm.quantity" type="number" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Notes</label>
          <textarea v-model="adjustmentForm.notes" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" rows="2"></textarea>
        </div>
      </form>
      <template #footer>
        <button @click="showAdjustModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleAdjust" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Submit</button>
      </template>
    </Modal>
  </div>
</template>
