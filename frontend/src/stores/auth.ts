import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/lib/axios'
import type { User, LoginRequest, RegisterRequest } from '@/types'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)
  const hasPermission = (permission: string) => {
    const perms = (user.value as any)?.permissions
    if (!perms || !Array.isArray(perms) || perms.length === 0) return true
    if (perms.includes('*') || perms.includes('admin')) return true
    return perms.includes(permission)
  }
  const hasRole = (role: string) => {
    const roles: any = (user.value as any)?.roles
    if (!roles) return true
    const names = Array.isArray(roles) ? roles.map((r: any) => (typeof r === 'string' ? r : r.name)) : []
    if (!names.length) return true
    return names.includes(role) || names.includes('Super Admin')
  }

  const syncOrg = (userData: any) => {
    const orgId = userData?.current_organization_id || userData?.organizations?.[0]?.id
    if (orgId) localStorage.setItem('organization_id', String(orgId))
  }

  const login = async (credentials: LoginRequest) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/auth/login', credentials)
      const { user: userData, token: authToken } = response.data.data
      user.value = userData
      token.value = authToken
      localStorage.setItem('token', authToken)
      syncOrg(userData)
      router.push('/')
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Login failed'
      throw e
    } finally {
      loading.value = false
    }
  }

  const register = async (data: RegisterRequest) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/auth/register', data)
      const { user: userData, token: authToken } = response.data.data
      user.value = userData
      token.value = authToken
      localStorage.setItem('token', authToken)
      syncOrg(userData)
      router.push('/')
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Registration failed'
      throw e
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      await api.post('/auth/logout')
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('token')
      localStorage.removeItem('organization_id')
      router.push('/login')
    }
  }

  const fetchUser = async () => {
    if (!token.value) return
    loading.value = true
    try {
      const response = await api.get('/auth/me')
      user.value = response.data.data
      syncOrg(user.value)
    } catch {
      token.value = null
      localStorage.removeItem('token')
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    hasPermission,
    hasRole,
    login,
    register,
    logout,
    fetchUser,
  }
})
