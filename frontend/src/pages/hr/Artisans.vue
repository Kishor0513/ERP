<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useHrStore } from '@/stores/hr'
import Modal from '@/components/Modal.vue'

const hrStore = useHrStore()
const showCreateModal = ref(false)
const editingArtisan = ref<any>(null)

const form = ref<{
  name: string
  skills: string[]
  hourly_rate: number
  phone: string
  address: string
  status: 'active' | 'inactive' | 'on_leave'
}>({
  name: '',
  skills: [] as string[],
  hourly_rate: 0,
  phone: '',
  address: '',
  status: 'active',
})

const skillOptions = ['Weaving', 'Dyeing', 'Cutting', 'Stitching', 'Embroidery', 'Finishing', 'Quality Control']

onMounted(async () => {
  await hrStore.fetchArtisans()
})

const openCreateModal = () => {
  editingArtisan.value = null
  form.value = { name: '', skills: [], hourly_rate: 0, phone: '', address: '', status: 'active' }
  showCreateModal.value = true
}

const openEditModal = (artisan: any) => {
  editingArtisan.value = artisan
  form.value = { ...artisan }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  if (editingArtisan.value) {
    await hrStore.updateArtisan(editingArtisan.value.id, form.value)
  } else {
    await hrStore.createArtisan(form.value)
  }
  showCreateModal.value = false
}

const handleDelete = async (id: number) => {
  if (confirm('Are you sure you want to delete this artisan?')) {
    await hrStore.deleteArtisan(id)
  }
}

const toggleSkill = (skill: string) => {
  const index = form.value.skills.indexOf(skill)
  if (index === -1) {
    form.value.skills.push(skill)
  } else {
    form.value.skills.splice(index, 1)
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Artisans Directory</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Manage artisan profiles, skills, and employment details</p>
      </div>
      <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Add Artisan</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="artisan in hrStore.artisans"
        :key="artisan.id"
        class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ artisan.name }}</h3>
          <span
            :class="artisan.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
            class="px-2 py-1 rounded-full text-xs font-medium"
          >
            {{ artisan.status }}
          </span>
        </div>
        <div class="mt-3">
          <p class="text-sm text-gray-500 dark:text-slate-400">Skills</p>
          <div class="flex flex-wrap gap-2 mt-1">
            <span v-for="skill in artisan.skills" :key="skill" class="px-2 py-1 bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded text-xs">
              {{ skill }}
            </span>
          </div>
        </div>
        <div class="mt-3">
          <p class="text-sm text-gray-500 dark:text-slate-400">Rate: ${{ artisan.hourly_rate }}/hr</p>
          <p class="text-sm text-gray-500 dark:text-slate-400">{{ artisan.phone }}</p>
        </div>
        <div class="mt-4 flex gap-2">
          <button @click="openEditModal(artisan)" class="text-sm text-primary-600 hover:text-primary-800">Edit</button>
          <button @click="handleDelete(artisan.id)" class="text-sm text-red-600 hover:text-red-800">Delete</button>
        </div>
      </div>
    </div>

    <Modal :open="showCreateModal" :title="editingArtisan ? 'Edit Artisan' : 'Add Artisan'" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Name</label>
          <input v-model="form.name" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Skills</label>
          <div class="flex flex-wrap gap-2 mt-1">
            <button
              v-for="skill in skillOptions"
              :key="skill"
              type="button"
              @click="toggleSkill(skill)"
              :class="[
                form.skills.includes(skill)
                  ? 'bg-primary-600 text-white'
                  : 'bg-gray-200 dark:bg-slate-600 text-gray-700 dark:text-slate-300',
                'px-3 py-1 rounded-full text-sm',
              ]"
            >
              {{ skill }}
            </button>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Hourly Rate</label>
            <input v-model.number="form.hourly_rate" type="number" step="0.01" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Phone</label>
            <input v-model="form.phone" type="tel" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Address</label>
          <textarea v-model="form.address" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" rows="2"></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Status</label>
          <select v-model="form.status" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="on_leave">On Leave</option>
          </select>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">{{ editingArtisan ? 'Update' : 'Create' }}</button>
      </template>
    </Modal>
  </div>
</template>
