<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProductionStore } from '@/stores/production'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import DataTable from '@/components/DataTable.vue'
import BulkBar from '@/components/BulkBar.vue'
import KanbanBoard from '@/components/KanbanBoard.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import { useList } from '@/composables/useList'

const router = useRouter()
const productionStore = useProductionStore()

const showCreateModal = ref(false)
const viewMode = ref<'table' | 'kanban'>('table')

const form = ref<{
  product_id: number | null
  quantity: number
  priority: 'low' | 'medium' | 'high' | 'urgent'
  due_date: string
  notes: string
}>({
  product_id: null,
  quantity: 0,
  priority: 'medium',
  due_date: '',
  notes: '',
})

const orderNo = (o: any) => o.production_order_number || o.order_number || `#${o.id}`
const orderQty = (o: any) => o.qty_ordered ?? o.quantity ?? '-'
const orderProduct = (o: any) => o.product_variant?.product?.name || o.product?.name || o.product_name || '-'

const columns = [
  { key: 'production_order_number', label: 'Order #', sortable: true, format: (_v: any, row: any) => orderNo(row) },
  { key: 'product', label: 'Product', format: (_v: any, row: any) => orderProduct(row) },
  { key: 'qty_ordered', label: 'Quantity', format: (_v: any, row: any) => String(orderQty(row)) },
  { key: 'priority', label: 'Priority' },
  { key: 'status', label: 'Status' },
  { key: 'due_date', label: 'Due Date', format: (v: string) => v ? new Date(v).toLocaleDateString() : '-' },
  { key: 'actions', label: 'Actions' },
]

const statusFilters = [
  {
    key: 'status',
    label: 'Status',
    type: 'select' as const,
    options: [
      { value: 'pending', label: 'Pending' },
      { value: 'materials_ready', label: 'Materials Ready' },
      { value: 'in_progress', label: 'In Progress' },
      { value: 'submitted_qc', label: 'Submitted QC' },
      { value: 'qc_passed', label: 'QC Passed' },
      { value: 'qc_failed', label: 'QC Failed' },
      { value: 'rework', label: 'Rework' },
      { value: 'completed', label: 'Completed' },
      { value: 'cancelled', label: 'Cancelled' },
    ],
  },
]

const kanbanColumns = [
  { key: 'pending', label: 'To Do', match: ['pending', 'materials_ready'] },
  { key: 'in_progress', label: 'In Progress', match: ['in_progress', 'rework'] },
  { key: 'submitted_qc', label: 'Quality Check', match: ['submitted_qc', 'qc_passed', 'qc_failed'] },
  { key: 'completed', label: 'Completed' },
  { key: 'cancelled', label: 'Cancelled' },
]

const { currentPage, perPage, filters, selected, load, onSearch, onPage, setFilter, toggleSelect, clearSelection, exportCsv } = useList(async (p) => {
  const q = p as unknown as Record<string, any>
  await productionStore.fetchProductionOrders({
    status: q.status || undefined,
    search: q.search || undefined,
    page: q.page,
    per_page: q.per_page,
  })
})

onMounted(async () => {
  await load()
})

const kanbanItems = computed(() =>
  productionStore.productionOrders.map((o: any) => ({
    ...o,
    order_number: orderNo(o),
    subtitle: `${orderProduct(o)} · Qty ${orderQty(o)}`,
  }))
)

const handleReset = () => {
  Object.keys(filters.value).forEach((k) => delete filters.value[k])
  currentPage.value = 1
  load()
}

const toggleSelectAll = () => {
  const ids = productionStore.productionOrders.map((o: any) => o.id)
  const all = ids.length > 0 && ids.every((id) => selected.value.includes(id))
  if (all) clearSelection()
  else selected.value = [...new Set([...selected.value, ...ids])]
}

const flatRows = (rows: any[]) => rows.map((o: any) => ({
  order_number: orderNo(o),
  product: orderProduct(o),
  quantity: orderQty(o),
  priority: o.priority,
  status: o.status,
  due_date: o.due_date || '',
}))

const exportSelected = () => {
  exportCsv(flatRows(productionStore.productionOrders.filter((o: any) => selected.value.includes(o.id))), 'production-orders')
}

const exportAll = () => {
  exportCsv(flatRows(productionStore.productionOrders), 'production-orders')
}

const openCreateModal = () => {
  form.value = { product_id: null, quantity: 0, priority: 'medium', due_date: '', notes: '' }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  await productionStore.createProductionOrder(form.value)
  showCreateModal.value = false
  load()
}

const viewOrder = (id: number) => {
  router.push(`/production/orders/${id}`)
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Production Orders" subtitle="Manage and track your production workflow">
      <template #actions>
        <div class="flex overflow-hidden rounded-lg border border-gray-300 dark:border-slate-600">
          <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300'" class="px-3 py-2 text-sm font-medium">Table</button>
          <button @click="viewMode = 'kanban'" :class="viewMode === 'kanban' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300'" class="px-3 py-2 text-sm font-medium">Kanban</button>
        </div>
        <button @click="exportAll" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Export</button>
        <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">New Order</button>
      </template>
    </PageHeader>

    <FilterBar :filters="statusFilters" :values="filters" @change="setFilter" @reset="handleReset" />

    <BulkBar :count="selected.length" @export="exportSelected" @clear="clearSelection" />

    <div v-if="viewMode === 'table'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="productionStore.productionOrders"
        :loading="productionStore.loading"
        :total-items="productionStore.totalOrders"
        :current-page="currentPage"
        :per-page="perPage"
        selectable
        :selected-ids="selected"
        @search="onSearch"
        @update:current-page="onPage"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" type="production" />
        </template>
        <template #cell-actions="{ row }">
          <button @click="viewOrder(row.id)" class="text-sm text-primary-600 hover:text-primary-800">View</button>
        </template>
      </DataTable>
    </div>

    <KanbanBoard
      v-else
      :columns="kanbanColumns"
      :items="kanbanItems"
      status-key="status"
      title-key="order_number"
      subtitle-key="subtitle"
      badge-type="production"
      @open="viewOrder"
    />

    <Modal :open="showCreateModal" title="Create Production Order" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Product ID</label>
          <input v-model.number="form.product_id" type="number" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Quantity</label>
            <input v-model.number="form.quantity" type="number" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Priority</label>
            <select v-model="form.priority" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Due Date</label>
          <input v-model="form.due_date" type="date" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Notes</label>
          <textarea v-model="form.notes" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" rows="2"></textarea>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Create</button>
      </template>
    </Modal>
  </div>
</template>
