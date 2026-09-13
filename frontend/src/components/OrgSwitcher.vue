<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useOrganizationStore } from '@/stores/organization'
import { ChevronUpDownIcon, CheckIcon, PlusIcon, BuildingOffice2Icon } from '@heroicons/vue/24/outline'

const orgStore = useOrganizationStore()
const open = ref(false)
const newName = ref('')
const creating = ref(false)

onMounted(async () => {
  try { await orgStore.fetchOrganizations() } catch {}
})

const select = async (id: number) => {
  open.value = false
  await orgStore.switchOrg(id)
}

const create = async () => {
  if (!newName.value.trim()) return
  creating.value = true
  try {
    await orgStore.create(newName.value.trim())
    newName.value = ''
    open.value = false
  } finally {
    creating.value = false
  }
}
</script>

<template>
  <div class="relative">
    <button @click="open = !open" class="w-full flex items-center gap-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 px-3 py-2 text-left transition-colors">
      <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-400 to-primary-700 flex items-center justify-center text-white shadow-sm shrink-0">
        <BuildingOffice2Icon class="w-4 h-4" />
      </span>
      <span class="flex-1 min-w-0">
        <span class="block text-[13px] font-semibold text-white truncate">{{ orgStore.organizations.find((o) => o.id === orgStore.currentId)?.name || 'Select company' }}</span>
        <span class="block text-[11px] text-slate-400 truncate">{{ orgStore.organizations.length }} workspace{{ orgStore.organizations.length === 1 ? '' : 's' }}</span>
      </span>
      <ChevronUpDownIcon class="w-4 h-4 text-slate-400 shrink-0" />
    </button>
    <div v-if="open" class="absolute z-50 mt-2 w-full rounded-xl bg-white dark:bg-slate-900 shadow-pop border border-slate-200 dark:border-slate-700 overflow-hidden">
      <div class="max-h-56 overflow-y-auto p-1.5 scrollbar-thin">
        <button v-for="o in orgStore.organizations" :key="o.id" @click="select(o.id)" class="w-full flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
          <span class="flex-1 truncate text-left text-slate-700 dark:text-slate-200">{{ o.name }}</span>
          <CheckIcon v-if="o.id === orgStore.currentId" class="w-4 h-4 text-primary-600" />
        </button>
      </div>
      <div class="border-t border-slate-100 dark:border-slate-800 p-2 flex gap-1.5">
        <input v-model="newName" placeholder="New company..." class="input !py-1.5 !text-[13px]" @keyup.enter="create" />
        <button @click="create" :disabled="creating || !newName.trim()" class="btn-primary !px-3 !py-1.5 !text-[13px] shrink-0">
          <PlusIcon class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>
