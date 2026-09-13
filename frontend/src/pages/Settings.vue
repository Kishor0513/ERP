<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import PageHeader from '@/components/PageHeader.vue'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const form = ref({ name: '', phone: '' })
const saved = ref(false)

onMounted(async () => {
  await authStore.fetchUser()
  form.value.name = authStore.user?.name || ''
  form.value.phone = (authStore.user as any)?.phone || ''
})

const handleSave = () => {
  saved.value = true
  window.setTimeout(() => {
    saved.value = false
  }, 2000)
}
</script>

<template>
  <div class="space-y-5 max-w-3xl">
    <PageHeader title="Settings" subtitle="Profile, appearance and workspace preferences." />
    <div class="card card-pad space-y-4">
      <div>
        <label class="label">Name</label>
        <input v-model="form.name" class="input max-w-sm" />
      </div>
      <div>
        <label class="label">Phone</label>
        <input v-model="form.phone" class="input max-w-sm" />
      </div>
      <div class="flex items-center gap-3">
        <button @click="handleSave" class="btn-primary">Save</button>
        <span v-if="saved" class="text-sm text-emerald-600">Saved</span>
      </div>
    </div>
    <div class="card card-pad flex items-center justify-between">
      <div>
        <p class="font-semibold text-slate-900 dark:text-white">Appearance</p>
        <p class="text-sm text-slate-500">Toggle dark mode.</p>
      </div>
      <button @click="themeStore.toggleTheme()" class="btn-secondary">{{ themeStore.isDark ? 'Light' : 'Dark' }} mode</button>
    </div>
    <div class="card card-pad flex items-center justify-between">
      <div>
        <p class="font-semibold text-slate-900 dark:text-white">Workspace</p>
        <p class="text-sm text-slate-500">Manage companies and billing.</p>
      </div>
      <div class="flex gap-2">
        <router-link to="/companies" class="btn-secondary">Companies</router-link>
        <router-link to="/settings/billing" class="btn-secondary">Billing</router-link>
      </div>
    </div>
  </div>
</template>
