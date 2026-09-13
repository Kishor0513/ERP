<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useCatalogStore } from '@/stores/catalog'
import PageHeader from '@/components/PageHeader.vue'
import DataTable from '@/components/DataTable.vue'
import Modal from '@/components/Modal.vue'

const catalogStore = useCatalogStore()
const showCreateModal = ref(false)
const editingAttribute = ref<any>(null)

const form = ref({ name: '', values: '' })
const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'values', label: 'Values' },
  { key: 'actions', label: 'Actions' },
]

onMounted(() => catalogStore.fetchAttributes())

const rows = computed(() => catalogStore.attributes as any[])

const openCreate = () => {
  editingAttribute.value = null
  form.value = { name: '', values: '' }
  showCreateModal.value = true
}

const openEdit = (row: any) => {
  editingAttribute.value = row
  form.value = { name: row.name, values: (row.values || []).map((v: any) => v.value).join(', ') }
  showCreateModal.value = true
}

const handleSave = async () => {
  const values = form.value.values.split(',').map((s) => s.trim()).filter(Boolean)
  if (editingAttribute.value) {
    await catalogStore.updateAttribute(editingAttribute.value.id, { name: form.value.name })
  } else {
    await catalogStore.createAttribute({ name: form.value.name, values } as any)
  }
  showCreateModal.value = false
}

const handleDelete = async (row: any) => {
  if (!confirm(`Delete "${row.name}"?`)) return
  await catalogStore.deleteAttribute(row.id)
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Attributes" subtitle="Manage product attributes and values.">
      <template #actions>
        <button class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500" @click="openCreate">New Attribute</button>
      </template>
    </PageHeader>
    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable :columns="columns" :data="rows" :loading="catalogStore.loading" :total-items="rows.length">
        <template #cell-values="{ row }">
          <span class="text-sm text-gray-600">{{ (row.values || []).map((v: any) => v.value).join(', ') }}</span>
        </template>
        <template #cell-actions="{ row }">
          <div class="flex gap-2">
            <button class="text-sm text-primary-600 hover:text-primary-800" @click="openEdit(row)">Edit</button>
            <button class="text-sm text-red-600 hover:text-red-800" @click="handleDelete(row)">Delete</button>
          </div>
        </template>
      </DataTable>
    </div>
    <Modal :open="showCreateModal" :title="editingAttribute ? 'Edit Attribute' : 'New Attribute'" @close="showCreateModal = false">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Name</label>
          <input v-model="form.name" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="e.g. Size" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Values (comma separated)</label>
          <input v-model="form.values" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="S, M, L" />
        </div>
      </div>
      <template #footer>
        <button class="rounded-lg border px-4 py-2.5 text-sm font-semibold" @click="showCreateModal = false">Cancel</button>
        <button class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white" @click="handleSave">Save</button>
      </template>
    </Modal>
  </div>
</template>
