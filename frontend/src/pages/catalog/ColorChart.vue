<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useCatalogStore } from '@/stores/catalog'
import Modal from '@/components/Modal.vue'

const catalogStore = useCatalogStore()
const showCreateModal = ref(false)
const editingColor = ref<any>(null)

const form = ref({
  name: '',
  code: '',
  hex_value: '#000000',
})

onMounted(async () => {
  await catalogStore.fetchColorChart()
})

const openCreateModal = () => {
  editingColor.value = null
  form.value = { name: '', code: '', hex_value: '#000000' }
  showCreateModal.value = true
}

const openEditModal = (color: any) => {
  editingColor.value = color
  form.value = { ...color }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  if (editingColor.value) {
    await catalogStore.updateColor(editingColor.value.id, form.value)
  } else {
    await catalogStore.createColor(form.value)
  }
  showCreateModal.value = false
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this color?')) {
    await catalogStore.deleteColor(id)
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Color Chart</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Manage your product color palette and hex codes</p>
      </div>
      <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Add Color</button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <div
        v-for="color in catalogStore.colorChart"
        :key="color.id"
        class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4 text-center"
      >
        <div
          class="w-16 h-16 rounded-full mx-auto mb-3 border-2 border-gray-200 dark:border-slate-600"
          :style="{ backgroundColor: color.hex_value }"
        />
        <p class="font-medium text-gray-900 dark:text-white">{{ color.name }}</p>
        <p class="text-sm text-gray-500 dark:text-slate-400">{{ color.code }}</p>
        <div class="mt-3 flex justify-center gap-2">
          <button @click="openEditModal(color)" class="text-sm text-primary-600 hover:text-primary-800">Edit</button>
          <button @click="handleDelete(color.id)" class="text-sm text-red-600 hover:text-red-800">Delete</button>
        </div>
      </div>
    </div>

    <Modal :open="showCreateModal" :title="editingColor ? 'Edit Color' : 'Add Color'" @close="showCreateModal = false">
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
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Hex Value</label>
          <div class="flex gap-2">
            <input v-model="form.hex_value" type="color" class="h-10 w-16 rounded-lg" />
            <input v-model="form.hex_value" type="text" class="flex-1 rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">{{ editingColor ? 'Update' : 'Create' }}</button>
      </template>
    </Modal>
  </div>
</template>
