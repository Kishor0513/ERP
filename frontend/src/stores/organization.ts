import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { Organization } from '@/types'

export const useOrganizationStore = defineStore('organization', () => {
  const organizations = ref<Organization[]>([])
  const currentId = ref<number | null>(Number(localStorage.getItem('organization_id')) || null)
  const billing = ref<any>(null)

  const fetchOrganizations = async () => {
    const res = await api.get('/organizations')
    organizations.value = res.data.data
    if (!currentId.value && organizations.value.length) {
      setCurrent(organizations.value[0].id)
    }
    return organizations.value
  }

  const setCurrent = (id: number) => {
    currentId.value = id
    localStorage.setItem('organization_id', String(id))
  }

  const create = async (name: string) => {
    const res = await api.post('/organizations', { name })
    organizations.value.push(res.data.data)
    setCurrent(res.data.data.id)
    return res.data.data
  }

  const switchOrg = async (id: number) => {
    await api.post(`/organizations/${id}/switch`)
    setCurrent(id)
    window.location.reload()
  }

  const fetchBilling = async () => {
    const res = await api.get('/billing')
    billing.value = res.data.data
    return billing.value
  }

  return { organizations, currentId, billing, fetchOrganizations, setCurrent, create, switchOrg, fetchBilling }
})
