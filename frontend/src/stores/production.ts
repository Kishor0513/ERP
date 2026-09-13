import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { ProductionOrder, Artisan, QcInspection } from '@/types'
import { parsePaginated } from '@/lib/paginated'

export const useProductionStore = defineStore('production', () => {
  const productionOrders = ref<ProductionOrder[]>([])
  const artisans = ref<Artisan[]>([])
  const qcInspections = ref<QcInspection[]>([])
  const loading = ref(false)
  const totalOrders = ref(0)
  const totalQc = ref(0)

  const fetchProductionOrders = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/production-orders', { params })
      const { items, total } = parsePaginated<ProductionOrder>(response)
      productionOrders.value = items
      totalOrders.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchProductionOrder = async (id: number): Promise<ProductionOrder> => {
    const response = await api.get(`/production-orders/${id}`)
    return response.data.data
  }

  const createProductionOrder = async (data: Partial<ProductionOrder>) => {
    const response = await api.post('/production-orders', data)
    productionOrders.value.unshift(response.data.data)
    return response.data.data
  }

  const updateProductionOrder = async (id: number, data: Partial<ProductionOrder>) => {
    const response = await api.put(`/production-orders/${id}`, data)
    const index = productionOrders.value.findIndex(o => o.id === id)
    if (index !== -1) {
      productionOrders.value[index] = response.data.data
    }
    return response.data.data
  }

  const assignArtisan = async (orderId: number, artisanId: number) => {
    const response = await api.post(`/production-orders/${orderId}/assign`, {
      artisan_id: artisanId,
    })
    const index = productionOrders.value.findIndex(o => o.id === orderId)
    if (index !== -1) {
      productionOrders.value[index] = response.data.data
    }
    return response.data.data
  }

  const submitQC = async (orderId: number, data: any) => {
    const response = await api.post(`/production-orders/${orderId}/qc`, data)
    const index = productionOrders.value.findIndex(o => o.id === orderId)
    if (index !== -1) {
      productionOrders.value[index] = response.data.data
    }
    return response.data.data
  }

  const fetchArtisans = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/artisans', { params })
      artisans.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const createArtisan = async (data: Partial<Artisan>) => {
    const response = await api.post('/artisans', data)
    artisans.value.push(response.data.data)
    return response.data.data
  }

  const updateArtisan = async (id: number, data: Partial<Artisan>) => {
    const response = await api.put(`/artisans/${id}`, data)
    const index = artisans.value.findIndex(a => a.id === id)
    if (index !== -1) {
      artisans.value[index] = response.data.data
    }
    return response.data.data
  }

  const deleteArtisan = async (id: number) => {
    await api.delete(`/artisans/${id}`)
    artisans.value = artisans.value.filter(a => a.id !== id)
  }

  const fetchQcInspections = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/qc-inspections', { params })
      const { items, total } = parsePaginated<QcInspection>(response)
      qcInspections.value = items
      totalQc.value = total
    } finally {
      loading.value = false
    }
  }

  const createQcInspection = async (data: Partial<QcInspection>) => {
    const response = await api.post('/qc-inspections', data)
    qcInspections.value.unshift(response.data.data)
    return response.data.data
  }

  const updateQcInspection = async (id: number, data: Partial<QcInspection>) => {
    const response = await api.put(`/qc-inspections/${id}`, data)
    const index = qcInspections.value.findIndex(q => q.id === id)
    if (index !== -1) qcInspections.value[index] = response.data.data
    return response.data.data
  }

  const deleteQcInspection = async (id: number) => {
    await api.delete(`/qc-inspections/${id}`)
    qcInspections.value = qcInspections.value.filter(q => q.id !== id)
  }

  return {
    productionOrders,
    artisans,
    qcInspections,
    loading,
    totalOrders,
    totalQc,
    fetchProductionOrders,
    fetchProductionOrder,
    createProductionOrder,
    updateProductionOrder,
    assignArtisan,
    submitQC,
    fetchArtisans,
    createArtisan,
    updateArtisan,
    deleteArtisan,
    fetchQcInspections,
    createQcInspection,
    updateQcInspection,
    deleteQcInspection,
  }
})
