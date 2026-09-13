import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import type { Product, Category, ColorChart, Attribute } from '@/types'
import { parsePaginated } from '@/lib/paginated'

export const useCatalogStore = defineStore('catalog', () => {
  const products = ref<Product[]>([])
  const categories = ref<Category[]>([])
  const categoryTree = ref<Category[]>([])
  const colorChart = ref<ColorChart[]>([])
  const attributes = ref<Attribute[]>([])
  const loading = ref(false)
  const totalProducts = ref(0)

  const fetchProducts = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/products', { params })
      const { items, total } = parsePaginated<Product>(response)
      products.value = items
      totalProducts.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchProduct = async (id: number): Promise<Product> => {
    const response = await api.get(`/products/${id}`)
    return response.data.data
  }

  const createProduct = async (data: Partial<Product>) => {
    const response = await api.post('/products', data)
    products.value.unshift(response.data.data)
    return response.data.data
  }

  const updateProduct = async (id: number, data: Partial<Product>) => {
    const response = await api.put(`/products/${id}`, data)
    const index = products.value.findIndex(p => p.id === id)
    if (index !== -1) {
      products.value[index] = response.data.data
    }
    return response.data.data
  }

  const deleteProduct = async (id: number) => {
    await api.delete(`/products/${id}`)
    products.value = products.value.filter(p => p.id !== id)
  }

  const addVariant = async (productId: number, data: any) => {
    const response = await api.post(`/products/${productId}/variants`, data)
    return response.data.data
  }

  const bulkImport = async (file: File) => {
    const formData = new FormData()
    formData.append('file', file)
    const response = await api.post('/products/bulk-import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return response.data.data
  }

  const fetchCategories = async () => {
    loading.value = true
    try {
      const response = await api.get('/categories')
      categories.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const fetchCategoryTree = async () => {
    const response = await api.get('/categories/tree')
    categoryTree.value = response.data.data
  }

  const createCategory = async (data: Partial<Category>) => {
    const response = await api.post('/categories', data)
    categories.value.push(response.data.data)
    return response.data.data
  }

  const updateCategory = async (id: number, data: Partial<Category>) => {
    const response = await api.put(`/categories/${id}`, data)
    const index = categories.value.findIndex(c => c.id === id)
    if (index !== -1) {
      categories.value[index] = response.data.data
    }
    return response.data.data
  }

  const deleteCategory = async (id: number) => {
    await api.delete(`/categories/${id}`)
    categories.value = categories.value.filter(c => c.id !== id)
  }

  const fetchColorChart = async () => {
    loading.value = true
    try {
      const response = await api.get('/color-chart')
      colorChart.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const createColor = async (data: Partial<ColorChart>) => {
    const response = await api.post('/color-chart', data)
    colorChart.value.push(response.data.data)
    return response.data.data
  }

  const updateColor = async (id: number, data: Partial<ColorChart>) => {
    const response = await api.put(`/color-chart/${id}`, data)
    const index = colorChart.value.findIndex(c => c.id === id)
    if (index !== -1) {
      colorChart.value[index] = response.data.data
    }
    return response.data.data
  }

  const deleteColor = async (id: number) => {
    await api.delete(`/color-chart/${id}`)
    colorChart.value = colorChart.value.filter(c => c.id !== id)
  }

  const fetchAttributes = async () => {
    loading.value = true
    try {
      const response = await api.get('/attributes')
      attributes.value = response.data.data
    } finally {
      loading.value = false
    }
  }

  const createAttribute = async (data: Partial<Attribute>) => {
    const response = await api.post('/attributes', data)
    attributes.value.push(response.data.data)
    return response.data.data
  }

  const updateAttribute = async (id: number, data: Partial<Attribute>) => {
    const response = await api.put(`/attributes/${id}`, data)
    const index = attributes.value.findIndex(a => a.id === id)
    if (index !== -1) {
      attributes.value[index] = response.data.data
    }
    return response.data.data
  }

  const deleteAttribute = async (id: number) => {
    await api.delete(`/attributes/${id}`)
    attributes.value = attributes.value.filter(a => a.id !== id)
  }

  return {
    products,
    categories,
    categoryTree,
    colorChart,
    attributes,
    loading,
    totalProducts,
    fetchProducts,
    fetchProduct,
    createProduct,
    updateProduct,
    deleteProduct,
    addVariant,
    bulkImport,
    fetchCategories,
    fetchCategoryTree,
    createCategory,
    updateCategory,
    deleteCategory,
    fetchColorChart,
    createColor,
    updateColor,
    deleteColor,
    fetchAttributes,
    createAttribute,
    updateAttribute,
    deleteAttribute,
  }
})
