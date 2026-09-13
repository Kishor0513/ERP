<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useOrganizationStore } from '@/stores/organization'
import PageHeader from '@/components/PageHeader.vue'
import EmptyState from '@/components/EmptyState.vue'

const orgStore = useOrganizationStore()
const loading = ref(false)
const newName = ref('')
const inviteUserId = ref('')
const inviteRole = ref('member')

onMounted(async () => {
  loading.value = true
  try {
    await orgStore.fetchOrganizations()
    await orgStore.fetchBilling()
  } finally {
    loading.value = false
  }
})

const create = async () => {
  if (!newName.value.trim()) return
  await orgStore.create(newName.value.trim())
  newName.value = ''
}

const current = () => orgStore.organizations.find((o) => o.id === orgStore.currentId)
</script>

<template>
  <div class="space-y-5">
    <PageHeader title="Companies" subtitle="Workspaces isolate data, members and billing per company." />

    <div class="card card-pad">
      <h2 class="text-sm font-semibold text-slate-900 dark:text-white">New company</h2>
      <div class="mt-3 flex gap-2">
        <input v-model="newName" placeholder="e.g. Acme Trading" class="input max-w-sm" @keyup.enter="create" />
        <button @click="create" class="btn-primary">Create</button>
      </div>
    </div>

    <div v-if="loading" class="grid sm:grid-cols-2 gap-4">
      <div v-for="i in 2" :key="i" class="skeleton h-40" />
    </div>
    <div v-else-if="!orgStore.organizations.length">
      <div class="card"><EmptyState title="No companies" hint="Create your first company workspace." /></div>
    </div>
    <div v-else class="grid sm:grid-cols-2 gap-4">
      <div v-for="o in orgStore.organizations" :key="o.id" class="card card-pad" :class="o.id === orgStore.currentId ? 'ring-2 ring-primary-500' : ''">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="font-semibold text-slate-900 dark:text-white">{{ o.name }}</p>
            <p class="text-xs text-slate-500">{{ o.slug }}</p>
          </div>
          <span v-if="o.id === orgStore.currentId" class="badge bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300">Current</span>
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
          <button v-if="o.id !== orgStore.currentId" @click="orgStore.switchOrg(o.id)" class="btn-secondary !py-2 !text-[13px]">Switch</button>
          <router-link to="/settings/billing" class="btn-ghost !py-2 !text-[13px]">Billing</router-link>
        </div>
        <div class="mt-4 border-t border-slate-100 dark:border-slate-800 pt-3 flex gap-2">
          <input v-model="inviteUserId" placeholder="User ID to invite" class="input !py-1.5 !text-[13px]" />
          <select v-model="inviteRole" class="input !py-1.5 !text-[13px] !w-32">
            <option value="admin">Admin</option>
            <option value="member">Member</option>
            <option value="viewer">Viewer</option>
          </select>
        </div>
        <p class="mt-1 text-[11px] text-slate-400">Current workspace: {{ current()?.name }} — invites use user IDs from team settings.</p>
      </div>
    </div>
  </div>
</template>
