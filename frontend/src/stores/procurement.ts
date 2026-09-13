import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { PurchaseOrder, Supplier } from '@/types'
import { parsePaginated } from '@/lib/paginated'

export const useProcurementStore = defineStore('procurement', () => {
  const purchaseOrders = ref<PurchaseOrder[]>([])
  const suppliers = ref<Supplier[]>([])
  const loading = ref(false)
  const totalPOs = ref(0)

  const fetchPurchaseOrders = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/purchase-orders', { params })
      const { items, total } = parsePaginated<PurchaseOrder>(response)
      purchaseOrders.value = items
      totalPOs.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchPurchaseOrder = async (id: number): Promise<PurchaseOrder> => {
    const response = await api.get(`/purchase-orders/${id}`)
    return response.data.data
  }

  const createPurchaseOrder = async (data: Partial<PurchaseOrder>) => {
    const response = await api.post('/purchase-orders', data)
    purchaseOrders.value.unshift(response.data.data)
    return response.data.data
  }

  const updatePurchaseOrder = async (id: number, data: Partial<PurchaseOrder>) => {
    const response = await api.put(`/purchase-orders/${id}`, data)
    const index = purchaseOrders.value.findIndex(po => po.id === id)
    if (index !== -1) {
      purchaseOrders.value[index] = response.data.data
    }
    return response.data.data
  }

  const receivePO = async (id: number, data: any) => {
    const response = await api.post(`/purchase-orders/${id}/receive`, data)
    const index = purchaseOrders.value.findIndex(po => po.id === id)
    if (index !== -1) {
      purchaseOrders.value[index] = response.data.data
    }
    return response.data.data
  }

  const fetchSuppliers = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/suppliers', { params })
      suppliers.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const createSupplier = async (data: Partial<Supplier>) => {
    const response = await api.post('/suppliers', data)
    suppliers.value.push(response.data.data)
    return response.data.data
  }

  const updateSupplier = async (id: number, data: Partial<Supplier>) => {
    const response = await api.put(`/suppliers/${id}`, data)
    const index = suppliers.value.findIndex(s => s.id === id)
    if (index !== -1) {
      suppliers.value[index] = response.data.data
    }
    return response.data.data
  }

  const deleteSupplier = async (id: number) => {
    await api.delete(`/suppliers/${id}`)
    suppliers.value = suppliers.value.filter(s => s.id !== id)
  }

  return {
    purchaseOrders,
    suppliers,
    loading,
    totalPOs,
    fetchPurchaseOrders,
    fetchPurchaseOrder,
    createPurchaseOrder,
    updatePurchaseOrder,
    receivePO,
    fetchSuppliers,
    createSupplier,
    updateSupplier,
    deleteSupplier,
  }
})
