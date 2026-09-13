<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useCrmStore } from '@/stores/crm'
import PageHeader from '@/components/PageHeader.vue'
import FilterBar from '@/components/FilterBar.vue'
import DataTable from '@/components/DataTable.vue'
import BulkBar from '@/components/BulkBar.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'

const crmStore = useCrmStore()

const statusFilter = ref('')
const sourceFilter = ref('')
const search = ref('')
const currentPage = ref(1)
const perPage = ref(15)
const showCreateModal = ref(false)
const viewMode = ref<'table' | 'kanban'>('kanban')
const selectedIds = ref<number[]>([])

const form = ref({
  name: '',
  company: '',
  email: '',
  phone: '',
  source: 'website',
  notes: '',
})

const filters = [
  { key: 'status', label: 'Status', type: 'select' as const, options: [
    { value: 'new', label: 'New' },
    { value: 'contacted', label: 'Contacted' },
    { value: 'qualified', label: 'Qualified' },
    { value: 'converted', label: 'Converted' },
    { value: 'lost', label: 'Lost' },
  ]},
  { key: 'source', label: 'Source', type: 'select' as const, options: [
    { value: 'website', label: 'Website' },
    { value: 'referral', label: 'Referral' },
    { value: 'social_media', label: 'Social Media' },
    { value: 'trade_show', label: 'Trade Show' },
    { value: 'cold_call', label: 'Cold Call' },
    { value: 'other', label: 'Other' },
  ]},
]

const filterValues = computed(() => ({ status: statusFilter.value, source: sourceFilter.value }))

const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'company', label: 'Company', sortable: true },
  { key: 'email', label: 'Email' },
  { key: 'phone', label: 'Phone' },
  { key: 'source', label: 'Source' },
  { key: 'status', label: 'Status' },
]

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  await crmStore.fetchLeads({
    status: statusFilter.value || undefined,
  })
}

const kanbanColumns = computed(() => [
  { key: 'new', label: 'New' },
  { key: 'contacted', label: 'Contacted' },
  { key: 'qualified', label: 'Qualified' },
  { key: 'converted', label: 'Converted' },
  { key: 'lost', label: 'Lost' },
])

const leadsFor = (status: string) => crmStore.leads.filter(l => l.status === status)

const handleFilterChange = (key: string, value: any) => {
  if (key === 'status') statusFilter.value = value
  if (key === 'source') sourceFilter.value = value
  currentPage.value = 1
  loadData()
}

const resetFilters = () => {
  statusFilter.value = ''
  sourceFilter.value = ''
  currentPage.value = 1
  loadData()
}

const handleSearch = (query: string) => {
  search.value = query
  currentPage.value = 1
  loadData()
}

const handlePageChange = (page: number) => {
  currentPage.value = page
  loadData()
}

const toggleSelect = (id: number) => {
  const i = selectedIds.value.indexOf(id)
  if (i >= 0) selectedIds.value.splice(i, 1)
  else selectedIds.value.push(id)
}

const toggleSelectAll = () => {
  const all = crmStore.leads.map(l => l.id)
  if (all.every(id => selectedIds.value.includes(id))) selectedIds.value = []
  else selectedIds.value = all
}

const exportCsv = () => {
  const rows = crmStore.leads.filter(l => selectedIds.value.includes(l.id))
  const data = rows.length ? rows : crmStore.leads
  if (!data.length) return
  const headers = ['id', 'name', 'company', 'email', 'phone', 'source', 'status']
  const esc = (v: any) => `"${String(v ?? '').replace(/"/g, '""')}"`
  const csv = [headers.join(','), ...data.map((r: any) => headers.map(h => esc(r[h])).join(','))].join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `leads-${new Date().toISOString().slice(0, 10)}.csv`
  a.click()
  URL.revokeObjectURL(url)
}

const openCreateModal = () => {
  form.value = { name: '', company: '', email: '', phone: '', source: 'website', notes: '' }
  showCreateModal.value = true
}

const handleSubmit = async () => {
  await crmStore.createLead(form.value)
  showCreateModal.value = false
  await loadData()
}

const handleConvert = async (id: number) => {
  if (confirm('Convert this lead to a wholesale account?')) {
    await crmStore.convertLead(id)
    loadData()
  }
}
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Leads" subtitle="Track and manage your sales leads pipeline">
      <template #actions>
        <div class="flex rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
          <button @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-white' : 'text-gray-500'" class="px-3 py-2 text-sm">Table</button>
          <button @click="viewMode = 'kanban'" :class="viewMode === 'kanban' ? 'bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-white' : 'text-gray-500'" class="px-3 py-2 text-sm">Kanban</button>
        </div>
        <button @click="openCreateModal" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Create Lead</button>
      </template>
    </PageHeader>

    <FilterBar :filters="filters" :values="filterValues" @change="handleFilterChange" @reset="resetFilters" />

    <BulkBar :count="selectedIds.length" @export="exportCsv" @clear="selectedIds = []" />

    <div v-if="viewMode === 'table'" class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
      <DataTable
        :columns="columns"
        :data="crmStore.leads"
        :loading="crmStore.loading"
        :total-items="crmStore.totalLeads"
        :current-page="currentPage"
        :per-page="perPage"
        selectable
        :selected-ids="selectedIds"
        @search="handleSearch"
        @update:current-page="handlePageChange"
        @toggle-select="toggleSelect"
        @toggle-select-all="toggleSelectAll"
      >
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" type="status" />
        </template>
      </DataTable>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
      <div
        v-for="column in kanbanColumns"
        :key="column.key"
        class="bg-gray-100 dark:bg-slate-700 rounded-2xl p-4"
      >
        <h3 class="font-medium text-gray-900 dark:text-white mb-4">
          {{ column.label }}
          <span class="text-sm text-gray-500 dark:text-slate-400">({{ leadsFor(column.key).length }})</span>
        </h3>
        <div class="space-y-3">
          <div
            v-for="lead in leadsFor(column.key)"
            :key="lead.id"
            class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-slate-600"
          >
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-900 dark:text-white">{{ lead.name }}</span>
              <StatusBadge :status="lead.status" type="status" />
            </div>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">{{ lead.company }}</p>
            <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">{{ lead.email }}</p>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-2">Source: {{ lead.source }}</p>
            <button
              v-if="lead.status === 'qualified'"
              @click="handleConvert(lead.id)"
              class="mt-3 text-sm text-primary-600 hover:text-primary-800"
            >
              Convert to Account →
            </button>
          </div>
        </div>
      </div>
    </div>

    <Modal :open="showCreateModal" title="Create Lead" @close="showCreateModal = false">
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Contact Name</label>
          <input v-model="form.name" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Company</label>
          <input v-model="form.company" type="text" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Email</label>
            <input v-model="form.email" type="email" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Phone</label>
            <input v-model="form.phone" type="tel" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Source</label>
          <select v-model="form.source" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm">
            <option value="website">Website</option>
            <option value="referral">Referral</option>
            <option value="social_media">Social Media</option>
            <option value="trade_show">Trade Show</option>
            <option value="cold_call">Cold Call</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Notes</label>
          <textarea v-model="form.notes" class="w-full rounded-lg border border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white px-3 py-2 text-sm" rows="3"></textarea>
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
        <button @click="handleSubmit" class="rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-500">Create</button>
      </template>
    </Modal>
  </div>
</template>
