<script setup lang="ts">
import { onMounted } from 'vue'
import { useAdminStore } from '@/stores/admin'
import PageHeader from '@/components/PageHeader.vue'
import DataTable from '@/components/DataTable.vue'

const adminStore = useAdminStore()
onMounted(() => adminStore.fetchRoles())
const columns = [
  { key: 'name', label: 'Role', sortable: true },
  { key: 'permissions', label: 'Permissions' },
]
</script>

<template>
  <div class="space-y-6">
    <PageHeader title="Roles & Permissions" subtitle="Available roles and permissions." />
    <div class="rounded-2xl bg-white dark:bg-slate-800 shadow-sm border overflow-hidden">
      <DataTable :columns="columns" :data="adminStore.roles" :total-items="adminStore.roles.length">
        <template #cell-permissions="{ row }">
          <span class="text-sm text-gray-600">{{ (row.permissions || []).map((p: any) => p.name).join(', ') }}</span>
        </template>
      </DataTable>
    </div>
  </div>
</template>
