<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCatalogStore } from '@/stores/catalog'
import DataTable from '@/components/DataTable.vue'
import Modal from '@/components/Modal.vue'
import FileUpload from '@/components/FileUpload.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import BulkBar from '@/components/BulkBar.vue'
import StatStrip from '@/components/StatStrip.vue'
import { useList } from '@/composables/useList'

const router = useRouter()
const catalogStore = useCatalogStore()

const { currentPage, perPage, filters, selected, setFilter, toggleSelect, clearSelection, exportCsv, onSearch, onSort, onPage, load } = useList((p: any) => catalogStore.fetchProducts(p))

const showCreateModal = ref(false)
const showImportModal = ref(false)
const editingProduct = ref<any>(null)
const viewMode = ref<'table' | 'grid'>('table')

const form = ref({
  name: '',
  sku: '',
  description: '',
  category_id: null,
  price: 0,
  cost_price: 0,
  stock_quantity: 0,
  low_stock_threshold: 10,
  status: 'active',
})

const columns = [
  { key: 'sku', label: 'SKU', sortable: true, class: 'w-32' },
  { key: 'name', label: 'Product', sortable: true },
  { key: 'category', label: 'Category' },
  { key: 'price', label: 'Price', sortable: true },
  { key: 'stock_quantity', label: 'Stock', sortable: true },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: 'Actions' },
]

const filterDefs = computed(() => [
  { key: 'status', label: 'Status', type: 'select' as const, options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
  { key: 'category_id', label: 'Category', type: 'select' as const, options: catalogStore.categories.map((c: any) => ({ value: String(c.id), label: c.name })) },
])

onMounted(async () => {
  await catalogStore.fetchCategories()
  await load()
})

const resetFilters = () => {
  filters.value = {}
  load()
}

const toggleSelectAll = () => {
  const rows = catalogStore.products || []
  const allSelected = rows.length > 0 && rows.every((r: any) => selected.value.includes(r.id))
  if (allSelected) clearSelection()
  else selected.value = rows.map((r: any) => r.id)
}

const handleExport = () => {
  const rows = (selected.value.length ? catalogStore.products.filter((p: any) => selected.value.includes(p.id)) : catalogStore.products) || []
  exportCsv(rows.map((p: any) => ({ id: p.id, sku: p.sku, name: p.name, category: p.category?.name || '', price: p.price, stock_quantity: p.stock_quantity, status: p.status })), 'products')
}

const handleBulkDelete = async () => {
  if (!selected.value.length) return
  if (!confirm(`Delete ${selected.value.length} products?`)) return
  for (const id of [...selected.value]) {
    await catalogStore.deleteProduct(id)
  }
  clearSelection()
  await load()
}

const openCreateModal = () => {
  editingProduct.value = null
  form.value = {
    name: '',
    sku: '',
    description: '',
    category_id: null,
    price: 0,
    cost_price: 0,
    stock_quantity: 0,
    low_stock_threshold: 10,
    status: 'active',
  }
  showCreateModal.value = true
}

const openEditModal = (product: any) => {
  editingProduct.value = product
  form.value = { ...product }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  if (editingProduct.value) {
    await catalogStore.updateProduct(editingProduct.value.id, form.value as any)
  } else {
    await catalogStore.createProduct(form.value as any)
  }
  showCreateModal.value = false
  load()
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this product?')) {
    await catalogStore.deleteProduct(id)
    load()
  }
}

const handleImport = async (files: File[]) => {
  if (files.length > 0) {
    await catalogStore.bulkImport(files[0])
    showImportModal.value = false
    load()
  }
}

const viewProduct = (id: number) => {
  router.push(`/catalog/products/${id}`)
}

const totalProducts = computed(() => catalogStore.totalProducts || catalogStore.products?.length || 0)
const lowStockCount = computed(() => catalogStore.products?.filter((p: any) => (p.stock_quantity ?? 0) <= (p.low_stock_threshold ?? 10)).length || 0)
const stats = computed(() => [
  { label: 'Total Products', value: totalProducts.value },
  { label: 'Low Stock', value: lowStockCount.value },
  { label: 'Categories', value: catalogStore.categories?.length || 0 },
])
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Products" subtitle="Manage your wool felt product catalog">
      <template #actions>
        <div class="flex rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
          <button
            @click="viewMode = 'table'"
            :class="viewMode === 'table' ? 'bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700'"
            class="p-2 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
          </button>
          <button
            @click="viewMode = 'grid'"
            :class="viewMode === 'grid' ? 'bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700'"
            class="p-2 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
            </svg>
          </button>
        </div>
        <button @click="handleExport" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
          Export CSV
        </button>
        <button @click="showImportModal = true" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
          Import
        </button>
        <button @click="openCreateModal" class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Add Product
        </button>
      </template>
    </PageHeader>

    <StatStrip :stats="stats" :loading="catalogStore.loading" />

    <FilterBar :filters="filterDefs" :values="filters" @change="setFilter" @reset="resetFilters" />

    <BulkBar :count="selected.length" @export="handleExport" @delete="handleBulkDelete" @clear="clearSelection" />

    <div v-if="viewMode === 'table'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="catalogStore.products"
        :loading="catalogStore.loading"
        :total-items="catalogStore.totalProducts"
        :current-page="currentPage"
        :per-page="perPage"
        :selectable="true"
        :selected-ids="selected"
        search-placeholder="Search products by name or SKU..."
        @search="onSearch"
        @sort="onSort"
        @update:current-page="onPage"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-sku="{ value }">
          <span class="font-mono text-xs bg-gray-100 dark:bg-slate-700 px-2 py-1 rounded-md">{{ value }}</span>
        </template>
        <template #cell-category="{ row }">
          <span class="text-sm text-gray-600 dark:text-slate-400">{{ row.category?.name || '-' }}</span>
        </template>
        <template #cell-price="{ value }">
          <span class="font-semibold text-gray-900 dark:text-white">${{ Number(value).toFixed(2) }}</span>
        </template>
        <template #cell-stock_quantity="{ value }">
          <span :class="value <= 10 ? 'text-red-600 font-semibold' : 'text-gray-900 dark:text-white'">
            {{ value }}
          </span>
        </template>
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" />
        </template>
        <template #cell-actions="{ row }">
          <div class="flex gap-2">
            <button @click.stop="openEditModal(row)" class="text-sm text-gray-600 hover:text-gray-800 dark:text-slate-400">Edit</button>
            <button @click.stop="handleDelete(row.id)" class="text-sm text-red-600 hover:text-red-800">Delete</button>
          </div>
        </template>
        <template #cell-name="{ row }">
          <button
            @click="viewProduct(row.id)"
            class="text-primary-600 hover:text-primary-800 font-medium hover:underline"
          >
            {{ row.name }}
          </button>
        </template>
      </DataTable>
    </div>

    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <div
        v-for="product in catalogStore.products"
        :key="product.id"
        class="group rounded-2xl bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 overflow-hidden hover:shadow-lg transition-all duration-200 cursor-pointer"
        @click="viewProduct(product.id)"
      >
        <div class="h-40 bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/20 dark:to-primary-800/20 flex items-center justify-center">
          <span class="text-5xl group-hover:scale-110 transition-transform duration-200">🧶</span>
        </div>
        <div class="p-4">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
              <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ product.name }}</h3>
              <p class="text-xs text-gray-500 dark:text-slate-400 font-mono mt-0.5">{{ product.sku }}</p>
            </div>
            <StatusBadge :status="product.status" />
          </div>
          <div class="mt-3 flex items-center justify-between">
            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">${{ Number(product.price).toFixed(2) }}</span>
            <span :class="product.stock_quantity <= 10 ? 'text-red-600' : 'text-gray-500'" class="text-sm">
              {{ product.stock_quantity }} in stock
            </span>
          </div>
        </div>
      </div>
    </div>

    <Modal :open="showCreateModal" :title="editingProduct ? 'Edit Product' : 'Create Product'" @close="showCreateModal = false" size="lg">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Product Name</label>
          <input v-model="form.name" type="text" class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" placeholder="e.g. Wool Felt Rug - Everest" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">SKU</label>
            <input v-model="form.sku" type="text" class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm font-mono" placeholder="FY-RUG-001" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Category</label>
            <select v-model="form.category_id" class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
              <option :value="null">Select category</option>
              <option v-for="cat in catalogStore.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Description</label>
          <textarea v-model="form.description" class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" rows="3" placeholder="Handcrafted wool felt product..."></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Selling Price ($)</label>
            <input v-model.number="form.price" type="number" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Cost Price ($)</label>
            <input v-model.number="form.cost_price" type="number" step="0.01" min="0" class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Stock Quantity</label>
            <input v-model.number="form.stock_quantity" type="number" min="0" class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Low Stock Alert</label>
            <input v-model.number="form.low_stock_threshold" type="number" min="0" class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Status</label>
          <select v-model="form.status" class="block w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
          {{ editingProduct ? 'Update Product' : 'Create Product' }}
        </button>
      </template>
    </Modal>

    <Modal :open="showImportModal" title="Import Products" @close="showImportModal = false">
      <FileUpload accept=".csv,.xlsx" @files="handleImport" />
      <template #footer>
        <button @click="showImportModal = false" class="rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600">Cancel</button>
      </template>
    </Modal>
  </div>
</template>
