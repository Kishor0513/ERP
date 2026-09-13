<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useHrStore } from '@/stores/hr'
import Modal from '@/components/Modal.vue'

const hrStore = useHrStore()
const showCreateModal = ref(false)
const editingRate = ref<any>(null)

const form = ref<{
  product_id: number | null
  operation: string
  rate: number
  unit: string
}>({
  product_id: null,
  operation: '',
  rate: 0,
  unit: 'piece',
})

onMounted(async () => {
  await hrStore.fetchPieceRates()
})

const openCreateModal = () => {
  editingRate.value = null
  form.value = { product_id: null, operation: '', rate: 0, unit: 'piece' }
  showCreateModal.value = true
}

const openEditModal = (rate: any) => {
  editingRate.value = rate
  form.value = { ...rate }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  if (editingRate.value) {
    await hrStore.updatePieceRate(editingRate.value.id, form.value)
  } else {
    await hrStore.createPieceRate(form.value)
  }
  showCreateModal.value = false
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this piece rate?')) {
    await hrStore.deletePieceRate(id)
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Piece Rates</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Define and manage piece rate compensation for artisans</p>
      </div>
      <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Add Piece Rate</button>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
        <thead class="bg-gray-50 dark:bg-slate-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Product ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Operation</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Rate</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Unit</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
          <tr v-if="hrStore.pieceRates.length === 0">
            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No piece rates found</td>
          </tr>
          <tr v-for="rate in hrStore.pieceRates" :key="rate.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ rate.product_id }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ rate.operation }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${{ rate.rate.toFixed(2) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ rate.unit }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <div class="flex gap-2">
                <button @click="openEditModal(rate)" class="text-primary-600 hover:text-primary-800">Edit</button>
                <button @click="handleDelete(rate.id)" class="text-red-600 hover:text-red-800">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Modal :open="showCreateModal" :title="editingRate ? 'Edit Piece Rate' : 'Add Piece Rate'" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Product ID</label>
          <input v-model.number="form.product_id" type="number" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Operation</label>
          <input v-model="form.operation" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Rate</label>
            <input v-model.number="form.rate" type="number" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Unit</label>
            <select v-model="form.unit" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm">
              <option value="piece">Piece</option>
              <option value="hour">Hour</option>
              <option value="meter">Meter</option>
            </select>
          </div>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">{{ editingRate ? 'Update' : 'Create' }}</button>
      </template>
    </Modal>
  </div>
</template>
