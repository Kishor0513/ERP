import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { Shipment } from '@/types'
import { parsePaginated } from '@/lib/paginated'

export const useLogisticsStore = defineStore('logistics', () => {
  const shipments = ref<Shipment[]>([])
  const loading = ref(false)
  const totalShipments = ref(0)

  const fetchShipments = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/shipments', { params })
      const { items, total } = parsePaginated<Shipment>(response)
      shipments.value = items
      totalShipments.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchShipment = async (id: number): Promise<Shipment> => {
    const response = await api.get(`/shipments/${id}`)
    return response.data.data
  }

  const createShipment = async (data: Partial<Shipment>) => {
    const response = await api.post('/shipments', data)
    shipments.value.unshift(response.data.data)
    return response.data.data
  }

  const updateShipment = async (id: number, data: Partial<Shipment>) => {
    const response = await api.put(`/shipments/${id}`, data)
    const index = shipments.value.findIndex(s => s.id === id)
    if (index !== -1) {
      shipments.value[index] = response.data.data
    }
    return response.data.data
  }

  const dispatchShipment = async (id: number) => {
    const response = await api.post(`/shipments/${id}/dispatch`)
    const index = shipments.value.findIndex(s => s.id === id)
    if (index !== -1) {
      shipments.value[index] = response.data.data
    }
    return response.data.data
  }

  const deliverShipment = async (id: number) => {
    const response = await api.post(`/shipments/${id}/deliver`)
    const index = shipments.value.findIndex(s => s.id === id)
    if (index !== -1) {
      shipments.value[index] = response.data.data
    }
    return response.data.data
  }

  return {
    shipments,
    loading,
    totalShipments,
    fetchShipments,
    fetchShipment,
    createShipment,
    updateShipment,
    dispatchShipment,
    deliverShipment,
  }
})
