import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { Artisan, PayrollRun, PieceRate } from '@/types'

export const useHrStore = defineStore('hr', () => {
  const artisans = ref<Artisan[]>([])
  const payrollRuns = ref<PayrollRun[]>([])
  const pieceRates = ref<PieceRate[]>([])
  const loading = ref(false)

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

  const fetchPayrollRuns = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/payroll-runs', { params })
      payrollRuns.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const fetchPayrollRun = async (id: number): Promise<PayrollRun> => {
    const response = await api.get(`/payroll-runs/${id}`)
    return response.data.data
  }

  const createPayrollRun = async (data: Partial<PayrollRun>) => {
    const response = await api.post('/payroll-runs', data)
    payrollRuns.value.unshift(response.data.data)
    return response.data.data
  }

  const approvePayrollRun = async (id: number) => {
    const response = await api.post(`/payroll-runs/${id}/approve`)
    const index = payrollRuns.value.findIndex(p => p.id === id)
    if (index !== -1) {
      payrollRuns.value[index] = response.data.data
    }
    return response.data.data
  }

  const payPayrollRun = async (id: number) => {
    const response = await api.post(`/payroll-runs/${id}/pay`)
    const index = payrollRuns.value.findIndex(p => p.id === id)
    if (index !== -1) {
      payrollRuns.value[index] = response.data.data
    }
    return response.data.data
  }

  const exportPayrollRun = async (id: number) => {
    const response = await api.get(`/payroll-runs/${id}/export`, {
      responseType: 'blob',
    })
    return response.data
  }

  const fetchPieceRates = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/piece-rates', { params })
      pieceRates.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const createPieceRate = async (data: Partial<PieceRate>) => {
    const response = await api.post('/piece-rates', data)
    pieceRates.value.push(response.data.data)
    return response.data.data
  }

  const updatePieceRate = async (id: number, data: Partial<PieceRate>) => {
    const response = await api.put(`/piece-rates/${id}`, data)
    const index = pieceRates.value.findIndex(p => p.id === id)
    if (index !== -1) {
      pieceRates.value[index] = response.data.data
    }
    return response.data.data
  }

  const deletePieceRate = async (id: number) => {
    await api.delete(`/piece-rates/${id}`)
    pieceRates.value = pieceRates.value.filter(p => p.id !== id)
  }

  return {
    artisans,
    payrollRuns,
    pieceRates,
    loading,
    fetchArtisans,
    createArtisan,
    updateArtisan,
    deleteArtisan,
    fetchPayrollRuns,
    fetchPayrollRun,
    createPayrollRun,
    approvePayrollRun,
    payPayrollRun,
    exportPayrollRun,
    fetchPieceRates,
    createPieceRate,
    updatePieceRate,
    deletePieceRate,
  }
})
