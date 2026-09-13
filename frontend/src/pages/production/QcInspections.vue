<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useProductionStore } from '@/stores/production'
import PageHeader from '@/components/PageHeader.vue'
import DataTable from '@/components/DataTable.vue'
import Modal from '@/components/Modal.vue'
import StatusBadge from '@/components/StatusBadge.vue'

const productionStore = useProductionStore()
const showCreateModal = ref(false)
const editingQc = ref<any>(null)
const form = ref({ production_order_id: '', artisan_id: '', result: 'pass' as 'pass' | 'fail' | 'rework', defect_reason: '', notes: '' })
const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'production_order_id', label: 'Order', sortable: true },
  { key: 'artisan_id', label: 'Artisan' },
  { key: 'result', label: 'Result' },
  { key: 'defect_reason', label: 'Defect' },
  { key: 'actions', label: 'Actions' },
]

onMounted(() => productionStore.fetchQcInspections())
const rows = computed(() => productionStore.qcInspections as any[])

const openCreate = () => {
  editingQc.value = null
  form.value = { production_order_id: '', artisan_id: '', result: 'pass', defect_reason: '', notes: '' }
  showCreateModal.value = true
}
const openEdit = (row: any) => {
  editingQc.value = row
  form.value = { production_order_id: String(row.production_order_id), artisan_id: String(row.artisan_id), result: row.result, defect_reason: row.defect_reason || '', notes: row.notes || '' }
  showCreateModal.value = true
}
const handleSave = async () => {
  if (editingQc.value) {
    await productionStore.updateQcInspection(editingQc.value.id, { result: form.value.result, defect_reason: form.value.defect_reason || null, notes: form.value.notes || null } as any)
  } else {
    await productionStore.createQcInspection({ production_order_id: Number(form.value.production_order_id), artisan_id: Number(form.value.artisan_id), result: form.value.result, defect_reason: form.value.defect_reason || null, notes: form.value.notes || null } as any)
  }
  showCreateModal.value = false
}
const handleDelete = async (row: any) => {
  if (!confirm(`Delete QC #${row.id}?`)) return
  await productionStore.deleteQcInspection(row.id)
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="QC Inspections" subtitle="Track quality checks per order and artisan.">
      <template #actions>
        <button class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500" @click="openCreate">New Inspection</button>
      </template>
    </PageHeader>
    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border overflow-hidden">
      <DataTable :columns="columns" :data="rows" :loading="productionStore.loading" :total-items="rows.length">
        <template #cell-result="{ row }">
          <StatusBadge :status="row.result === 'pass' ? 'qc_passed' : row.result === 'fail' ? 'qc_failed' : 'submitted_qc'" :label="row.result" />
        </template>
        <template #cell-actions="{ row }">
          <div class="flex gap-2">
            <button class="text-sm text-primary-600" @click="openEdit(row)">Edit</button>
            <button class="text-sm text-red-600" @click="handleDelete(row)">Delete</button>
          </div>
        </template>
      </DataTable>
    </div>
    <Modal :open="showCreateModal" :title="editingQc ? 'Edit Inspection' : 'New Inspection'" @close="showCreateModal = false">
      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-medium mb-1">Order ID</label><input v-model="form.production_order_id" type="number" class="w-full rounded-lg border px-3 py-2 text-sm" :disabled="!!editingQc" /></div>
          <div><label class="block text-sm font-medium mb-1">Artisan ID</label><input v-model="form.artisan_id" type="number" class="w-full rounded-lg border px-3 py-2 text-sm" :disabled="!!editingQc" /></div>
        </div>
        <div><label class="block text-sm font-medium mb-1">Result</label><select v-model="form.result" class="w-full rounded-lg border px-3 py-2 text-sm"><option value="pass">Pass</option><option value="fail">Fail</option><option value="rework">Rework</option></select></div>
        <div><label class="block text-sm font-medium mb-1">Defect reason</label><input v-model="form.defect_reason" class="w-full rounded-lg border px-3 py-2 text-sm" /></div>
        <div><label class="block text-sm font-medium mb-1">Notes</label><textarea v-model="form.notes" class="w-full rounded-lg border px-3 py-2 text-sm" rows="3" /></div>
      </div>
      <template #footer>
        <button class="rounded-lg border px-4 py-2.5 text-sm font-semibold" @click="showCreateModal = false">Cancel</button>
        <button class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white" @click="handleSave">Save</button>
      </template>
    </Modal>
  </div>
</template>
