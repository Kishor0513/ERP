<script setup lang="ts">
import { ref, onMounted } from 'vue'
import Sidebar from '@/components/Sidebar.vue'
import TopBar from '@/components/TopBar.vue'
import Breadcrumbs from '@/components/Breadcrumbs.vue'
import { useOrganizationStore } from '@/stores/organization'

const sidebarOpen = ref(false)
const orgStore = useOrganizationStore()

onMounted(async () => {
  try {
    await orgStore.fetchOrganizations()
    await orgStore.fetchBilling()
  } catch {}
})
</script>

<template>
  <div class="min-h-screen bg-slate-100 dark:bg-slate-950">
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden" @click="sidebarOpen = false" />
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 transition-transform duration-300 lg:translate-x-0">
      <Sidebar />
    </aside>

    <div class="lg:pl-72 min-h-screen flex flex-col">
      <TopBar @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      <div v-if="orgStore.billing && !orgStore.billing.subscribed && !orgStore.billing.on_trial" class="bg-amber-50 dark:bg-amber-950/40 border-b border-amber-200 dark:border-amber-900 px-4 sm:px-6 py-2.5 text-[13px] text-amber-800 dark:text-amber-200 flex items-center justify-between gap-3">
        <span>Trial ended — subscribe to keep your workspace active.</span>
        <router-link to="/settings/billing" class="font-semibold underline underline-offset-2">View billing</router-link>
      </div>

      <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <Breadcrumbs />
        <router-view />
      </main>
    </div>
  </div>
</template>
