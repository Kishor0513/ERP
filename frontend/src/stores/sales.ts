import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { SalesOrder, WholesaleAccount } from '@/types'
import { parsePaginated } from '@/lib/paginated'

export const useSalesStore = defineStore('sales', () => {
  const salesOrders = ref<SalesOrder[]>([])
  const wholesaleAccounts = ref<WholesaleAccount[]>([])
  const loading = ref(false)
  const totalOrders = ref(0)
  const totalAccounts = ref(0)

  const fetchSalesOrders = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/sales-orders', { params })
      const { items, total } = parsePaginated<SalesOrder>(response)
      salesOrders.value = items
      totalOrders.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchSalesOrder = async (id: number): Promise<SalesOrder> => {
    const response = await api.get(`/sales-orders/${id}`)
    return response.data.data
  }

  const createSalesOrder = async (data: Partial<SalesOrder>) => {
    const response = await api.post('/sales-orders', data)
    salesOrders.value.unshift(response.data.data)
    return response.data.data
  }

  const updateSalesOrder = async (id: number, data: Partial<SalesOrder>) => {
    const response = await api.put(`/sales-orders/${id}`, data)
    const index = salesOrders.value.findIndex(o => o.id === id)
    if (index !== -1) {
      salesOrders.value[index] = response.data.data
    }
    return response.data.data
  }

  const shipOrder = async (id: number, data: any) => {
    const response = await api.post(`/sales-orders/${id}/ship`, data)
    const index = salesOrders.value.findIndex(o => o.id === id)
    if (index !== -1) {
      salesOrders.value[index] = response.data.data
    }
    return response.data.data
  }

  const cancelOrder = async (id: number) => {
    const response = await api.post(`/sales-orders/${id}/cancel`)
    const index = salesOrders.value.findIndex(o => o.id === id)
    if (index !== -1) {
      salesOrders.value[index] = response.data.data
    }
    return response.data.data
  }

  const fetchWholesaleAccounts = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/wholesale-accounts', { params })
      const { items, total } = parsePaginated<WholesaleAccount>(response)
      wholesaleAccounts.value = items
      totalAccounts.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchWholesaleAccount = async (id: number): Promise<WholesaleAccount> => {
    const response = await api.get(`/wholesale-accounts/${id}`)
    return response.data.data
  }

  const createWholesaleAccount = async (data: Partial<WholesaleAccount>) => {
    const response = await api.post('/wholesale-accounts', data)
    wholesaleAccounts.value.unshift(response.data.data)
    return response.data.data
  }

  const updateWholesaleAccount = async (id: number, data: Partial<WholesaleAccount>) => {
    const response = await api.put(`/wholesale-accounts/${id}`, data)
    const index = wholesaleAccounts.value.findIndex(a => a.id === id)
    if (index !== -1) {
      wholesaleAccounts.value[index] = response.data.data
    }
    return response.data.data
  }

  const approveAccount = async (id: number) => {
    const response = await api.post(`/wholesale-accounts/${id}/approve`)
    const index = wholesaleAccounts.value.findIndex(a => a.id === id)
    if (index !== -1) {
      wholesaleAccounts.value[index] = response.data.data
    }
    return response.data.data
  }

  const rejectAccount = async (id: number) => {
    const response = await api.post(`/wholesale-accounts/${id}/reject`)
    const index = wholesaleAccounts.value.findIndex(a => a.id === id)
    if (index !== -1) {
      wholesaleAccounts.value[index] = response.data.data
    }
    return response.data.data
  }

  return {
    salesOrders,
    wholesaleAccounts,
    loading,
    totalOrders,
    totalAccounts,
    fetchSalesOrders,
    fetchSalesOrder,
    createSalesOrder,
    updateSalesOrder,
    shipOrder,
    cancelOrder,
    fetchWholesaleAccounts,
    fetchWholesaleAccount,
    createWholesaleAccount,
    updateWholesaleAccount,
    approveAccount,
    rejectAccount,
  }
})
