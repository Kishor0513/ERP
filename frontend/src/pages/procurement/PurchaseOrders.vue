<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useProcurementStore } from '@/stores/procurement'
import { useInventoryStore } from '@/stores/inventory'
import DataTable from '@/components/DataTable.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import BulkBar from '@/components/BulkBar.vue'

const procurementStore = useProcurementStore()
const inventoryStore = useInventoryStore()

const search = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const showCreateModal = ref(false)
const selectedIds = ref<number[]>([])
const filterValues = ref<Record<string, any>>({ status: '' })

const form = ref({
  supplier_id: null as number | null,
  expected_date: '',
  notes: '',
  items: [{ raw_material_id: null as number | null, qty: 1, unit_cost: 0 }],
})

const columns = [
  { key: 'po_number', label: 'PO #', sortable: true },
  { key: 'supplier', label: 'Supplier', format: (v: any) => v?.name || '-' },
  { key: 'total', label: 'Total', format: (v: number) => `$${Number(v).toFixed(2)}` },
  { key: 'status', label: 'Status' },
  { key: 'expected_delivery', label: 'Expected', format: (v: string) => v ? new Date(v).toLocaleDateString() : '-' },
]

const filters = [
  { key: 'status', label: 'Status', type: 'select' as const, options: [
    { value: 'draft', label: 'Draft' },
    { value: 'submitted', label: 'Submitted' },
    { value: 'confirmed', label: 'Confirmed' },
    { value: 'partially_received', label: 'Partially Received' },
    { value: 'received', label: 'Received' },
    { value: 'cancelled', label: 'Cancelled' },
  ] },
]

onMounted(async () => {
  await loadData()
  await procurementStore.fetchSuppliers()
  await inventoryStore.fetchRawMaterials({ per_page: 100 })
})

const loadData = async () => {
  await procurementStore.fetchPurchaseOrders({
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
  const all = procurementStore.purchaseOrders.map((r: any) => r.id)
  const allSelected = all.length > 0 && all.every((id) => selectedIds.value.includes(id))
  selectedIds.value = allSelected ? [] : all
}

const exportCsv = () => {
  const rows = procurementStore.purchaseOrders.filter((r: any) => selectedIds.value.length === 0 || selectedIds.value.includes(r.id))
  const header = ['po_number', 'supplier', 'total', 'status', 'expected_delivery']
  const lines = [header.join(',')]
  for (const r of rows as any[]) {
    lines.push([r.po_number, r.supplier?.name || '', r.total, r.status, r.expected_delivery || r.expected_date || ''].map((v) => `"${String(v ?? '').replace(/"/g, '""')}"`).join(','))
  }
  const blob = new Blob([lines.join('\n')], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'purchase-orders.csv'
  a.click()
  URL.revokeObjectURL(url)
}

const openCreateModal = () => {
  form.value = {
    supplier_id: null,
    expected_date: '',
    notes: '',
    items: [{ raw_material_id: null, qty: 1, unit_cost: 0 }],
  }
  showCreateModal.value = true
}

const addItem = () => {
  form.value.items.push({ raw_material_id: null, qty: 1, unit_cost: 0 })
}

const removeItem = (index: number) => {
  form.value.items.splice(index, 1)
}

const handleSubmit = async () => {
  await procurementStore.createPurchaseOrder({
    supplier_id: form.value.supplier_id!,
    expected_delivery: form.value.expected_date,
    notes: form.value.notes,
    items: form.value.items.map(i => ({
      raw_material_id: i.raw_material_id!,
      quantity: i.qty,
      unit_price: i.unit_cost,
    })) as any,
  })
  showCreateModal.value = false
  await loadData()
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Purchase Orders" subtitle="Create and manage purchase orders for raw materials">
      <template #actions>
        <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Create Purchase Order</button>
      </template>
    </PageHeader>

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="handleFilterReset" />

    <BulkBar :count="selectedIds.length" @export="exportCsv" @clear="selectedIds = []" />

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="procurementStore.purchaseOrders"
        :loading="procurementStore.loading"
        :total-items="procurementStore.totalPOs"
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
          <StatusBadge :status="row.status" type="order" />
        </template>
      </DataTable>
    </div>

    <Modal :open="showCreateModal" title="Create Purchase Order" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Supplier</label>
          <select v-model="form.supplier_id" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required>
            <option :value="null" disabled>Select supplier</option>
            <option v-for="s in procurementStore.suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Expected Delivery Date</label>
          <input v-model="form.expected_date" type="date" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Notes</label>
          <textarea v-model="form.notes" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" rows="2"></textarea>
        </div>
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300">Items</label>
            <button type="button" @click="addItem" class="text-sm text-primary-600 hover:text-primary-800">+ Add Item</button>
          </div>
          <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-3 gap-2 mb-2">
            <select v-model="item.raw_material_id" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required>
              <option :value="null" disabled>Material</option>
              <option v-for="m in inventoryStore.rawMaterials" :key="m.id" :value="m.id">{{ m.name }}</option>
            </select>
            <input v-model.number="item.qty" type="number" min="1" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" placeholder="Qty" required />
            <div class="flex gap-1">
              <input v-model.number="item.unit_cost" type="number" step="0.01" min="0" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" placeholder="Unit cost" required />
              <button v-if="form.items.length > 1" type="button" @click="removeItem(i)" class="text-red-500 hover:text-red-700 px-1">✕</button>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Create</button>
      </template>
    </Modal>
  </div>
</template>
