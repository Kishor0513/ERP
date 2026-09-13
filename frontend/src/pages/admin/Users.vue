<script setup lang="ts">
import { onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import PageHeader from '@/components/PageHeader.vue'
import DataTable from '@/components/DataTable.vue'

const adminStore = useAdminStore()
onMounted(() => adminStore.fetchUsers())
const columns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'email', label: 'Email' },
  { key: 'roles', label: 'Roles' },
]
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Users" subtitle="Members of the current organization." />
    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border overflow-hidden">
      <DataTable :columns="columns" :data="adminStore.users" :loading="adminStore.loading" :total-items="adminStore.totalUsers">
        <template #cell-roles="{ row }">
          <span class="text-sm text-gray-600">{{ (row.roles || []).map((r: any) => r.name).join(', ') }}</span>
        </template>
      </DataTable>
    </div>
  </div>
</template>
