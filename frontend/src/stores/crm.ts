import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { Lead, Quote } from '@/types'
import { parsePaginated } from '@/lib/paginated'

export const useCrmStore = defineStore('crm', () => {
  const leads = ref<Lead[]>([])
  const quotes = ref<Quote[]>([])
  const loading = ref(false)
  const totalLeads = ref(0)
  const totalQuotes = ref(0)

  const fetchLeads = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/leads', { params })
      const { items, total } = parsePaginated<Lead>(response)
      leads.value = items
      totalLeads.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchLead = async (id: number): Promise<Lead> => {
    const response = await api.get(`/leads/${id}`)
    return response.data.data
  }

  const createLead = async (data: Partial<Lead>) => {
    const response = await api.post('/leads', data)
    leads.value.unshift(response.data.data)
    return response.data.data
  }

  const updateLead = async (id: number, data: Partial<Lead>) => {
    const response = await api.put(`/leads/${id}`, data)
    const index = leads.value.findIndex(l => l.id === id)
    if (index !== -1) {
      leads.value[index] = response.data.data
    }
    return response.data.data
  }

  const convertLead = async (id: number) => {
    const response = await api.post(`/leads/${id}/convert`)
    const index = leads.value.findIndex(l => l.id === id)
    if (index !== -1) {
      leads.value[index] = response.data.data
    }
    return response.data.data
  }

  const fetchQuotes = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/quotes', { params })
      const { items, total } = parsePaginated<Quote>(response)
      quotes.value = items
      totalQuotes.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchQuote = async (id: number): Promise<Quote> => {
    const response = await api.get(`/quotes/${id}`)
    return response.data.data
  }

  const createQuote = async (data: Partial<Quote>) => {
    const response = await api.post('/quotes', data)
    quotes.value.unshift(response.data.data)
    return response.data.data
  }

  const updateQuote = async (id: number, data: Partial<Quote>) => {
    const response = await api.put(`/quotes/${id}`, data)
    const index = quotes.value.findIndex(q => q.id === id)
    if (index !== -1) {
      quotes.value[index] = response.data.data
    }
    return response.data.data
  }

  const sendQuote = async (id: number) => {
    const response = await api.post(`/quotes/${id}/send`)
    const index = quotes.value.findIndex(q => q.id === id)
    if (index !== -1) {
      quotes.value[index] = response.data.data
    }
    return response.data.data
  }

  const acceptQuote = async (id: number) => {
    const response = await api.post(`/quotes/${id}/accept`)
    const index = quotes.value.findIndex(q => q.id === id)
    if (index !== -1) {
      quotes.value[index] = response.data.data
    }
    return response.data.data
  }

  const convertToOrder = async (id: number) => {
    const response = await api.post(`/quotes/${id}/convert-to-order`)
    return response.data.data
  }

  return {
    leads,
    quotes,
    loading,
    totalLeads,
    totalQuotes,
    fetchLeads,
    fetchLead,
    createLead,
    updateLead,
    convertLead,
    fetchQuotes,
    fetchQuote,
    createQuote,
    updateQuote,
    sendQuote,
    acceptQuote,
    convertToOrder,
  }
})
