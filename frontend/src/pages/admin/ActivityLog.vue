<script setup lang="ts">
import { onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import PageHeader from '@/components/PageHeader.vue'
import DataTable from '@/components/DataTable.vue'

const adminStore = useAdminStore()
onMounted(() => adminStore.fetchActivities())
const columns = [
  { key: 'description', label: 'Action', sortable: true },
  { key: 'subject_type', label: 'Subject' },
  { key: 'causer', label: 'By' },
  { key: 'created_at', label: 'At' },
]
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Activity Log" subtitle="Recent system activity." />
    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border overflow-hidden">
      <DataTable :columns="columns" :data="adminStore.activities" :loading="adminStore.loading" :total-items="adminStore.totalActivities">
        <template #cell-causer="{ row }">
          <span class="text-sm">{{ row.causer?.email || row.causer_id || '-' }}</span>
        </template>
      </DataTable>
    </div>
  </div>
</template>
