<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useOrganizationStore } from '@/stores/organization'
import PageHeader from '@/components/PageHeader.vue'
import StatStrip from '@/components/StatStrip.vue'
import api from '@/lib/axios'

const orgStore = useOrganizationStore()
const loading = ref(false)
const checkoutLoading = ref('')
const plans = ref<any>({})

onMounted(async () => {
  loading.value = true
  try {
    const b = await orgStore.fetchBilling()
    plans.value = b?.plans || {}
  } finally {
    loading.value = false
  }
})

const checkout = async (plan: string, interval: 'monthly' | 'yearly') => {
  checkoutLoading.value = plan + interval
  try {
    const res = await api.post('/billing/checkout', {
      plan,
      interval,
      success_url: window.location.href,
      cancel_url: window.location.href,
    })
    if (res.data.data.url) window.location.href = res.data.data.url
  } finally {
    checkoutLoading.value = ''
  }
}

const portal = async () => {
  const res = await api.post('/billing/portal', { return_url: window.location.href })
  if (res.data.data.url) window.location.href = res.data.data.url
}
</script>

<template>
  <div class="space-y-5">
    <PageHeader title="Billing" subtitle="Trial, subscription and plan management per company.">
      <template #actions>
        <button @click="portal" class="btn-secondary">Customer portal</button>
      </template>
    </PageHeader>

    <StatStrip :loading="loading" :stats="[
      { label: 'Status', value: orgStore.billing?.subscribed ? 'Subscribed' : orgStore.billing?.on_trial ? 'Trial' : 'Inactive' },
      { label: 'Trial ends', value: orgStore.billing?.trial_ends_at ? new Date(orgStore.billing.trial_ends_at).toLocaleDateString() : '—' },
      { label: 'Plans', value: Object.keys(plans).length || 3 },
    ]" />

    <div class="grid md:grid-cols-3 gap-4">
      <div v-for="(p, key) in (Object.keys(plans).length ? plans : { starter: { name: 'Starter' }, growth: { name: 'Growth' }, enterprise: { name: 'Enterprise' } })" :key="key" class="card card-pad flex flex-col">
        <p class="font-semibold text-slate-900 dark:text-white">{{ p.name || key }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ (p.features || []).join(' · ') || 'Full ERP access' }}</p>
        <div class="mt-4 flex gap-2">
          <button @click="checkout(String(key), 'monthly')" :disabled="!!checkoutLoading" class="btn-primary !py-2 !text-[13px] flex-1">Monthly</button>
          <button @click="checkout(String(key), 'yearly')" :disabled="!!checkoutLoading" class="btn-secondary !py-2 !text-[13px] flex-1">Yearly</button>
        </div>
      </div>
    </div>
  </div>
</template>
