import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { RawMaterial, StockMovement, Warehouse } from '@/types'
import { parsePaginated } from '@/lib/paginated'

export const useInventoryStore = defineStore('inventory', () => {
  const rawMaterials = ref<RawMaterial[]>([])
  const stockMovements = ref<StockMovement[]>([])
  const warehouses = ref<Warehouse[]>([])
  const lowStockItems = ref<RawMaterial[]>([])
  const loading = ref(false)
  const totalRawMaterials = ref(0)
  const totalMovements = ref(0)

  const fetchRawMaterials = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/raw-materials', { params })
      const { items, total } = parsePaginated<RawMaterial>(response)
      rawMaterials.value = items
      totalRawMaterials.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchRawMaterial = async (id: number): Promise<RawMaterial> => {
    const response = await api.get(`/raw-materials/${id}`)
    return response.data.data
  }

  const createRawMaterial = async (data: Partial<RawMaterial>) => {
    const response = await api.post('/raw-materials', data)
    rawMaterials.value.unshift(response.data.data)
    return response.data.data
  }

  const updateRawMaterial = async (id: number, data: Partial<RawMaterial>) => {
    const response = await api.put(`/raw-materials/${id}`, data)
    const index = rawMaterials.value.findIndex(r => r.id === id)
    if (index !== -1) {
      rawMaterials.value[index] = response.data.data
    }
    return response.data.data
  }

  const deleteRawMaterial = async (id: number) => {
    await api.delete(`/raw-materials/${id}`)
    rawMaterials.value = rawMaterials.value.filter(r => r.id !== id)
  }

  const fetchLowStock = async () => {
    const response = await api.get('/raw-materials/low-stock')
    lowStockItems.value = response.data.data
  }

  const fetchStockMovements = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/stock-movements', { params })
      const { items, total } = parsePaginated<StockMovement>(response)
      stockMovements.value = items
      totalMovements.value = total
    } finally {
      loading.value = false
    }
  }

  const createStockMovement = async (data: Partial<StockMovement>) => {
    const response = await api.post('/stock-movements', data)
    stockMovements.value.unshift(response.data.data)
    return response.data.data
  }

  const fetchStockAlerts = async () => {
    const response = await api.get('/stock-movements/alerts')
    return response.data.data
  }

  const fetchWarehouses = async () => {
    loading.value = true
    try {
      const response = await api.get('/warehouses')
      warehouses.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const createWarehouse = async (data: Partial<Warehouse>) => {
    const response = await api.post('/warehouses', data)
    warehouses.value.push(response.data.data)
    return response.data.data
  }

  const updateWarehouse = async (id: number, data: Partial<Warehouse>) => {
    const response = await api.put(`/warehouses/${id}`, data)
    const index = warehouses.value.findIndex(w => w.id === id)
    if (index !== -1) {
      warehouses.value[index] = response.data.data
    }
    return response.data.data
  }

  const deleteWarehouse = async (id: number) => {
    await api.delete(`/warehouses/${id}`)
    warehouses.value = warehouses.value.filter(w => w.id !== id)
  }

  return {
    rawMaterials,
    stockMovements,
    warehouses,
    lowStockItems,
    loading,
    totalRawMaterials,
    totalMovements,
    fetchRawMaterials,
    fetchRawMaterial,
    createRawMaterial,
    updateRawMaterial,
    deleteRawMaterial,
    fetchLowStock,
    fetchStockMovements,
    createStockMovement,
    fetchStockAlerts,
    fetchWarehouses,
    createWarehouse,
    updateWarehouse,
    deleteWarehouse,
  }
})
