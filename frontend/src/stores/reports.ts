import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { DashboardData } from '@/types'

export const useReportsStore = defineStore('reports', () => {
  const dashboard = ref<DashboardData | null>(null)
  const salesSummary = ref<any>(null)
  const productionSummary = ref<any>(null)
  const inventorySummary = ref<any>(null)
  const loading = ref(false)

  const fetchDashboard = async () => {
    loading.value = true
    try {
      const response = await api.get('/reports/dashboard')
      dashboard.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const fetchSalesSummary = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/reports/sales-summary', { params })
      salesSummary.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const fetchProductionSummary = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/reports/production-summary', { params })
      productionSummary.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const fetchInventorySummary = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/reports/inventory-summary', { params })
      inventorySummary.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  return {
    dashboard,
    salesSummary,
    productionSummary,
    inventorySummary,
    loading,
    fetchDashboard,
    fetchSalesSummary,
    fetchProductionSummary,
    fetchInventorySummary,
  }
})
