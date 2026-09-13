<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductionStore } from '@/stores/production'
import StatusBadge from '@/components/StatusBadge.vue'

const route = useRoute()
const router = useRouter()
const productionStore = useProductionStore()

const order = ref<any>(null)
const activeTab = ref('info')
const showAssignModal = ref(false)
const showQCModal = ref(false)

const qcForm = ref({
  status: 'passed',
  defects: [],
  notes: '',
})

onMounted(async () => {
  const id = Number(route.params.id)
  order.value = await productionStore.fetchProductionOrder(id)
  await productionStore.fetchArtisans()
})

const handleAssign = async (artisanId: number) => {
  await productionStore.assignArtisan(order.value.id, artisanId)
  order.value = await productionStore.fetchProductionOrder(order.value.id)
  showAssignModal.value = false
}

const handleQC = async () => {
  await productionStore.submitQC(order.value.id, qcForm.value)
  order.value = await productionStore.fetchProductionOrder(order.value.id)
  showQCModal.value = false
}
</script>

<template>
  <div class="space-y-6" v-if="order">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <button
          @click="router.back()"
          class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mb-2"
        >
          ← Back to Production Orders
        </button>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ order.order_number }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">{{ order.product?.name }}</p>
      </div>
      <div class="flex gap-2">
        <button @click="showAssignModal = true" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Assign Artisan</button>
        <button @click="showQCModal = true" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Submit QC</button>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Status</p>
        <StatusBadge :status="order.status" type="production" />
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Quantity</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ order.quantity }}</p>
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Priority</p>
        <StatusBadge :status="order.priority" type="status" />
      </div>
      <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Due Date</p>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ order.due_date ? new Date(order.due_date).toLocaleDateString() : '-' }}
        </p>
      </div>
    </div>

    <div class="border-b border-gray-200 dark:border-slate-700">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in ['info', 'artisan', 'qc']"
          :key="tab"
          @click="activeTab = tab"
          :class="[
            activeTab === tab
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm capitalize',
          ]"
        >
          {{ tab === 'qc' ? 'QC History' : tab }}
        </button>
      </nav>
    </div>

    <div v-if="activeTab === 'info'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <div class="grid grid-cols-2 gap-6">
        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Order Number</h3>
          <p class="mt-1 text-gray-900 dark:text-white">{{ order.order_number }}</p>
        </div>
        <div>
          <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400">Notes</h3>
          <p class="mt-1 text-gray-900 dark:text-white">{{ order.notes || 'No notes' }}</p>
        </div>
      </div>
    </div>

    <div v-if="activeTab === 'artisan'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <div v-if="order.assigned_artisan">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Assigned Artisan</h3>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-500 dark:text-slate-400">Name</p>
            <p class="text-gray-900 dark:text-white">{{ order.assigned_artisan.name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-slate-400">Skills</p>
            <div class="flex gap-2 mt-1">
              <span v-for="skill in order.assigned_artisan.skills" :key="skill" class="px-2 py-1 bg-primary-100 text-primary-800 rounded text-xs">
                {{ skill }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <p v-else class="text-gray-500 dark:text-slate-400">No artisan assigned</p>
    </div>

    <div v-if="activeTab === 'qc'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 p-6">
      <div v-if="order.qc_results.length === 0" class="text-center py-12">
        <p class="text-gray-500 dark:text-slate-400">No QC inspections yet</p>
      </div>
      <div v-else class="space-y-4">
        <div v-for="qc in order.qc_results" :key="qc.id" class="border-b border-gray-200 dark:border-slate-700 pb-4">
          <div class="flex items-center justify-between">
            <StatusBadge :status="qc.status" type="production" />
            <span class="text-sm text-gray-500 dark:text-slate-400">{{ new Date(qc.created_at).toLocaleDateString() }}</span>
          </div>
          <p class="mt-2 text-sm text-gray-600 dark:text-slate-300">{{ qc.notes }}</p>
        </div>
      </div>
    </div>

    <Modal :open="showAssignModal" title="Assign Artisan" @close="showAssignModal = false">
      <div class="space-y-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Select an artisan to assign to this order:</p>
        <div class="space-y-2">
          <button
            v-for="artisan in productionStore.artisans"
            :key="artisan.id"
            @click="handleAssign(artisan.id)"
            class="w-full p-3 text-left bg-gray-50 dark:bg-slate-700 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600"
          >
            <p class="font-medium text-gray-900 dark:text-white">{{ artisan.name }}</p>
            <p class="text-sm text-gray-500 dark:text-slate-400">{{ artisan.skills.join(', ') }}</p>
          </button>
        </div>
      </div>
      <template #footer>
        <button @click="showAssignModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
      </template>
    </Modal>

    <Modal :open="showQCModal" title="Submit QC Inspection" @close="showQCModal = false">
      <form @submit.prevent="handleQC" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Result</label>
          <select v-model="qcForm.status" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm">
            <option value="passed">Passed</option>
            <option value="failed">Failed</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Notes</label>
          <textarea v-model="qcForm.notes" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" rows="3"></textarea>
        </div>
      </form>
      <template #footer>
        <button @click="showQCModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleQC" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Submit</button>
      </template>
    </Modal>
  </div>
</template>
