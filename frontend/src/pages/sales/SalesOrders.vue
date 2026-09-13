<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useSalesStore } from '@/stores/sales'
import { useCatalogStore } from '@/stores/catalog'
import DataTable from '@/components/DataTable.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import BulkBar from '@/components/BulkBar.vue'
import KanbanBoard from '@/components/KanbanBoard.vue'
import { useList } from '@/composables/useList'

const router = useRouter()
const salesStore = useSalesStore()
const catalogStore = useCatalogStore()

const { currentPage, perPage, filters, selected, setFilter, toggleSelect, clearSelection, exportCsv, onSearch, onPage, load } = useList((p: any) => salesStore.fetchSalesOrders(p))

const showCreateModal = ref(false)
const viewMode = ref<'table' | 'kanban'>('table')

const form = ref({
  channel: 'online' as 'online' | 'wholesale' | 'retail' | 'marketplace',
  wholesale_account_id: null as number | null,
  notes: '',
  items: [{ product_variant_id: null as number | null, qty: 1, unit_price: 0 }],
})

const columns = [
  { key: 'order_number', label: 'Order #', sortable: true },
  { key: 'customer_name', label: 'Customer', sortable: true },
  { key: 'channel', label: 'Channel' },
  { key: 'total', label: 'Total', format: (v: number) => `$${v.toFixed(2)}` },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Date', format: (v: string) => new Date(v).toLocaleDateString() },
]

const filterDefs = computed(() => [
  { key: 'status', label: 'Status', type: 'select' as const, options: ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'].map(s => ({ value: s, label: s })) },
  { key: 'channel', label: 'Channel', type: 'select' as const, options: ['online', 'wholesale', 'retail', 'marketplace'].map(s => ({ value: s, label: s })) },
])

const kanbanColumns = [
  { key: 'pending', label: 'Pending' },
  { key: 'confirmed', label: 'Confirmed' },
  { key: 'processing', label: 'Processing' },
  { key: 'shipped', label: 'Shipped' },
  { key: 'delivered', label: 'Delivered' },
  { key: 'cancelled', label: 'Cancelled' },
]

onMounted(async () => {
  await load()
  await salesStore.fetchWholesaleAccounts()
  await catalogStore.fetchProducts({ per_page: 100 })
})

const resetFilters = () => {
  filters.value = {}
  load()
}

const toggleSelectAll = () => {
  const rows = salesStore.salesOrders || []
  const allSelected = rows.length > 0 && rows.every((r: any) => selected.value.includes(r.id))
  if (allSelected) clearSelection()
  else selected.value = rows.map((r: any) => r.id)
}

const handleExport = () => {
  const rows = (selected.value.length ? salesStore.salesOrders.filter((o: any) => selected.value.includes(o.id)) : salesStore.salesOrders) || []
  exportCsv(rows.map((o: any) => ({ id: o.id, order_number: o.order_number, customer_name: o.customer_name, channel: o.channel, total: o.total, status: o.status, created_at: o.created_at })), 'sales-orders')
}

const handleBulkDelete = async () => {
  if (!selected.value.length) return
  if (!confirm(`Cancel ${selected.value.length} orders?`)) return
  for (const id of [...selected.value]) {
    await salesStore.cancelOrder(id)
  }
  clearSelection()
  await load()
}

const viewOrder = (id: number) => {
  router.push(`/sales/orders/${id}`)
}

const openCreateModal = () => {
  form.value = {
    channel: 'online',
    wholesale_account_id: null,
    notes: '',
    items: [{ product_variant_id: null, qty: 1, unit_price: 0 }],
  }
  showCreateModal.value = true
}

const addItem = () => {
  form.value.items.push({ product_variant_id: null, qty: 1, unit_price: 0 })
}

const removeItem = (index: number) => {
  form.value.items.splice(index, 1)
}

const handleSubmit = async () => {
  await salesStore.createSalesOrder({
    channel: form.value.channel,
    wholesale_account_id: form.value.wholesale_account_id || undefined,
    notes: form.value.notes,
    items: form.value.items.map(i => ({
      variant_id: i.product_variant_id,
      quantity: i.qty,
      unit_price: i.unit_price,
    })) as any,
  } as any)
  showCreateModal.value = false
  await load()
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Sales Orders" subtitle="Track and manage customer orders">
      <template #actions>
        <div class="flex rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
          <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-white' : 'text-gray-500'" class="px-3 py-2 text-sm font-medium transition-colors">Table</button>
          <button @click="viewMode = 'kanban'" :class="viewMode === 'kanban' ? 'bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-white' : 'text-gray-500'" class="px-3 py-2 text-sm font-medium transition-colors">Kanban</button>
        </div>
        <button @click="handleExport" class="btn-secondary">Export CSV</button>
        <button @click="openCreateModal" class="btn-primary">Create Order</button>
      </template>
    </PageHeader>

    <FilterBar :filters="filterDefs" :values="filters" @change="setFilter" @reset="resetFilters" />

    <BulkBar :count="selected.length" @export="handleExport" @delete="handleBulkDelete" @clear="clearSelection" />

    <KanbanBoard
      v-if="viewMode === 'kanban'"
      :columns="kanbanColumns"
      :items="salesStore.salesOrders"
      status-key="status"
      title-key="order_number"
      subtitle-key="customer_name"
      badge-type="order"
      @open="viewOrder"
    />

    <DataTable
      v-else
      :columns="columns"
      :data="salesStore.salesOrders"
      :loading="salesStore.loading"
      :total-items="salesStore.totalOrders"
      :current-page="currentPage"
      :per-page="perPage"
      :selectable="true"
      :selected-ids="selected"
      @search="onSearch"
      @update:current-page="onPage"
      @toggle-select="toggleSelect"
      @toggle-select-all="toggleSelectAll"
    >
      <template #cell-order_number="{ row }">
        <button
          @click="viewOrder(row.id)"
          class="text-primary-600 hover:text-primary-800 font-medium"
        >
          {{ row.order_number }}
        </button>
      </template>
      <template #cell-channel="{ row }">
        <span class="capitalize">{{ row.channel }}</span>
      </template>
      <template #cell-status="{ row }">
        <StatusBadge :status="row.status" type="order" />
      </template>
    </DataTable>

    <Modal :open="showCreateModal" title="Create Sales Order" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="label">Channel</label>
          <select v-model="form.channel" class="input" required>
            <option value="online">Online</option>
            <option value="wholesale">Wholesale</option>
            <option value="retail">Retail</option>
            <option value="marketplace">Marketplace</option>
          </select>
        </div>
        <div v-if="form.channel === 'wholesale'">
          <label class="label">Wholesale Account (optional)</label>
          <select v-model="form.wholesale_account_id" class="input">
            <option :value="null">None</option>
            <option v-for="a in salesStore.wholesaleAccounts" :key="a.id" :value="a.id">{{ a.company_name }}</option>
          </select>
        </div>
        <div>
          <label class="label">Notes</label>
          <textarea v-model="form.notes" class="input" rows="2"></textarea>
        </div>
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="label">Items</label>
            <button type="button" @click="addItem" class="text-sm text-primary-600 hover:text-primary-800">+ Add Item</button>
          </div>
          <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-3 gap-2 mb-2">
            <select v-model="item.product_variant_id" class="input" required>
              <option :value="null" disabled>Product</option>
              <option v-for="p in catalogStore.products" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <input v-model.number="item.qty" type="number" min="1" class="input" placeholder="Qty" required />
            <div class="flex gap-1">
              <input v-model.number="item.unit_price" type="number" step="0.01" min="0" class="input" placeholder="Unit price" required />
              <button v-if="form.items.length > 1" type="button" @click="removeItem(i)" class="text-red-500 hover:text-red-700 px-1">✕</button>
            </div>
          </div>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="btn-secondary">Cancel</button>
        <button @click="handleSubmit" class="btn-primary">Create</button>
      </template>
    </Modal>
  </div>
</template>
