<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useCatalogStore } from '@/stores/catalog'
import Modal from '@/components/Modal.vue'
import DataTable from '@/components/DataTable.vue'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import BulkBar from '@/components/BulkBar.vue'
import { useList } from '@/composables/useList'

const catalogStore = useCatalogStore()
const showCreateModal = ref(false)
const editingCategory = ref<any>(null)

const { filters, selected, setFilter, toggleSelect, clearSelection, exportCsv, load } = useList(async () => {
  await catalogStore.fetchCategories()
})

const form = ref({
  name: '',
  parent_id: null,
})

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'parent', label: 'Parent' },
  { key: 'actions', label: 'Actions' },
]

const filterDefs = computed(() => [
  { key: 'search', label: 'Search', type: 'text' as const, placeholder: 'Filter by name...' },
  { key: 'parent_id', label: 'Parent', type: 'select' as const, options: catalogStore.categories.map((c: any) => ({ value: String(c.id), label: c.name })) },
])

const filteredCategories = computed(() => {
  let rows = catalogStore.categories || []
  const q = String(filters.value.search || '').toLowerCase()
  if (q) rows = rows.filter((c: any) => c.name?.toLowerCase().includes(q))
  if (filters.value.parent_id) rows = rows.filter((c: any) => String(c.parent_id) === String(filters.value.parent_id))
  return rows
})

onMounted(async () => {
  await catalogStore.fetchCategoryTree()
  await load()
})

const resetFilters = () => {
  filters.value = {}
  load()
}

const toggleSelectAll = () => {
  const rows = filteredCategories.value || []
  const allSelected = rows.length > 0 && rows.every((r: any) => selected.value.includes(r.id))
  if (allSelected) clearSelection()
  else selected.value = rows.map((r: any) => r.id)
}

const parentName = (id: any) => {
  if (!id) return '-'
  return catalogStore.categories.find((c: any) => c.id === id)?.name || '-'
}

const handleExport = () => {
  const rows = (selected.value.length ? filteredCategories.value.filter((c: any) => selected.value.includes(c.id)) : filteredCategories.value) || []
  exportCsv(rows.map((c: any) => ({ id: c.id, name: c.name, parent_id: c.parent_id || '', parent: parentName(c.parent_id) })), 'categories')
}

const handleBulkDelete = async () => {
  if (!selected.value.length) return
  if (!confirm(`Delete ${selected.value.length} categories?`)) return
  for (const id of [...selected.value]) {
    await catalogStore.deleteCategory(id)
  }
  clearSelection()
  await catalogStore.fetchCategoryTree()
  await load()
}

const openCreateModal = (parent?: any) => {
  editingCategory.value = null
  form.value = {
    name: '',
    parent_id: parent?.id || null,
  }
  showCreateModal.value = true
}

const openEditModal = (category: any) => {
  editingCategory.value = category
  form.value = { ...category }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  if (editingCategory.value) {
    await catalogStore.updateCategory(editingCategory.value.id, form.value)
  } else {
    await catalogStore.createCategory(form.value)
  }
  showCreateModal.value = false
  await catalogStore.fetchCategoryTree()
  await load()
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this category?')) {
    await catalogStore.deleteCategory(id)
    await catalogStore.fetchCategoryTree()
    await load()
  }
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Categories" subtitle="Organize your products into categories and subcategories">
      <template #actions>
        <button @click="handleExport" class="rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600">Export CSV</button>
        <button @click="openCreateModal()" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Add Category</button>
      </template>
    </PageHeader>

    <FilterBar :filters="filterDefs" :values="filters" @change="setFilter" @reset="resetFilters" />

    <BulkBar :count="selected.length" @export="handleExport" @delete="handleBulkDelete" @clear="clearSelection" />

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="filteredCategories"
        :loading="catalogStore.loading"
        :total-items="filteredCategories.length"
        :current-page="1"
        :per-page="filteredCategories.length || 15"
        :selectable="true"
        :selected-ids="selected"
        :searchable="false"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-parent="{ row }">
          <span class="text-sm text-gray-600 dark:text-slate-400">{{ parentName(row.parent_id) }}</span>
        </template>
        <template #cell-actions="{ row }">
          <div class="flex gap-2">
            <button @click="openCreateModal(row)" class="text-sm text-primary-600 hover:text-primary-800">Add Child</button>
            <button @click="openEditModal(row)" class="text-sm text-gray-600 hover:text-gray-800 dark:text-slate-400">Edit</button>
            <button @click="handleDelete(row.id)" class="text-sm text-red-600 hover:text-red-800">Delete</button>
          </div>
        </template>
      </DataTable>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <div v-if="catalogStore.categoryTree.length === 0" class="text-center py-12">
        <p class="text-gray-500 dark:text-slate-400">No categories found</p>
      </div>
      <ul v-else class="space-y-2">
        <li v-for="category in catalogStore.categoryTree" :key="category.id" class="border-b border-gray-200 dark:border-slate-700 pb-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="font-medium text-gray-900 dark:text-white">{{ category.name }}</span>
            </div>
            <div class="flex gap-2">
              <button @click="openCreateModal(category)" class="text-sm text-primary-600 hover:text-primary-800">Add Child</button>
              <button @click="openEditModal(category)" class="text-sm text-gray-600 hover:text-gray-800 dark:text-slate-400">Edit</button>
              <button @click="handleDelete(category.id)" class="text-sm text-red-600 hover:text-red-800">Delete</button>
            </div>
          </div>
          <ul v-if="category.children?.length" class="mt-2 ml-6 space-y-2">
            <li v-for="child in category.children" :key="child.id" class="flex items-center justify-between">
              <span class="text-gray-600 dark:text-slate-400">{{ child.name }}</span>
              <div class="flex gap-2">
                <button @click="openEditModal(child)" class="text-sm text-gray-600 hover:text-gray-800 dark:text-slate-400">Edit</button>
                <button @click="handleDelete(child.id)" class="text-sm text-red-600 hover:text-red-800">Delete</button>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </div>

    <Modal :open="showCreateModal" :title="editingCategory ? 'Edit Category' : 'Create Category'" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Name</label>
          <input v-model="form.name" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Parent Category</label>
          <select v-model="form.parent_id" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm">
            <option :value="null">None (Top Level)</option>
            <option v-for="cat in catalogStore.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">{{ editingCategory ? 'Update' : 'Create' }}</button>
      </template>
    </Modal>
  </div>
</template>
