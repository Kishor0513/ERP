import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/lib/axios'
import { parsePaginated } from '@/lib/paginated'

export const useAdminStore = defineStore('admin', () => {
  const users = ref<any[]>([])
  const roles = ref<any[]>([])
  const permissions = ref<string[]>([])
  const activities = ref<any[]>([])
  const loading = ref(false)
  const totalUsers = ref(0)
  const totalActivities = ref(0)

  const fetchUsers = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/admin/users', { params })
      const { items, total } = parsePaginated<any>(response)
      users.value = items
      totalUsers.value = total
    } finally {
      loading.value = false
    }
  }

  const fetchRoles = async () => {
    const response = await api.get('/admin/roles')
    roles.value = response.data.data.roles || []
    permissions.value = response.data.data.permissions || []
  }

  const fetchActivities = async (params?: Record<string, any>) => {
    loading.value = true
    try {
      const response = await api.get('/admin/activity-log', { params })
      const { items, total } = parsePaginated<any>(response)
      activities.value = items
      totalActivities.value = total
    } finally {
      loading.value = false
    }
  }

  return { users, roles, permissions, activities, loading, totalUsers, totalActivities, fetchUsers, fetchRoles, fetchActivities }
})
