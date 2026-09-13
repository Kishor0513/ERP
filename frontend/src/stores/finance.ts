import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { Invoice, Expense } from '@/types'
import { parsePaginated } from '@/lib/paginated'

export const useFinanceStore = defineStore('finance', () => {
  const invoices = ref<Invoice[]>([])
  const expenses = ref<Expense[]>([])
  const loading = ref(false)
  const totalInvoices = ref(0)
  const totalExpenses = ref(0)

  const fetchInvoices = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/invoices', { params })
      const { items, total } = parsePaginated<Invoice>(response)
      invoices.value = items
      totalInvoices.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchInvoice = async (id: number): Promise<Invoice> => {
    const response = await api.get(`/invoices/${id}`)
    return response.data.data
  }

  const createInvoice = async (data: Partial<Invoice>) => {
    const response = await api.post('/invoices', data)
    invoices.value.unshift(response.data.data)
    return response.data.data
  }

  const updateInvoice = async (id: number, data: Partial<Invoice>) => {
    const response = await api.put(`/invoices/${id}`, data)
    const index = invoices.value.findIndex(i => i.id === id)
    if (index !== -1) {
      invoices.value[index] = response.data.data
    }
    return response.data.data
  }

  const recordPayment = async (id: number, data: any) => {
    const response = await api.post(`/invoices/${id}/record-payment`, data)
    const index = invoices.value.findIndex(i => i.id === id)
    if (index !== -1) {
      invoices.value[index] = response.data.data
    }
    return response.data.data
  }

  const fetchExpenses = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/expenses', { params })
      const { items, total } = parsePaginated<Expense>(response)
      expenses.value = items
      totalExpenses.value = total
    } finally {
      loading.value = false
    }
  }

  const createExpense = async (data: Partial<Expense>) => {
    const response = await api.post('/expenses', data)
    expenses.value.unshift(response.data.data)
    return response.data.data
  }

  const updateExpense = async (id: number, data: Partial<Expense>) => {
    const response = await api.put(`/expenses/${id}`, data)
    const index = expenses.value.findIndex(e => e.id === id)
    if (index !== -1) {
      expenses.value[index] = response.data.data
    }
    return response.data.data
  }

  const deleteExpense = async (id: number) => {
    await api.delete(`/expenses/${id}`)
    expenses.value = expenses.value.filter(e => e.id !== id)
  }

  return {
    invoices,
    expenses,
    loading,
    totalInvoices,
    totalExpenses,
    fetchInvoices,
    fetchInvoice,
    createInvoice,
    updateInvoice,
    recordPayment,
    fetchExpenses,
    createExpense,
    updateExpense,
    deleteExpense,
  }
})
