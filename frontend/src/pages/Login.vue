<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const form = ref({
  email: 'admin@feltandyarn.com',
  password: 'password',
})
const loading = ref(false)
const error = ref<string | null>(null)

const handleSubmit = async () => {
  loading.value = true
  error.value = null
  try {
    await authStore.login(form.value)
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Login failed. Please check your credentials.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex">
    <!-- Left side - Branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-900 via-slate-800 to-primary-900 relative overflow-hidden">
      <!-- Decorative wool felt pattern -->
      <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 800 600" fill="none">
          <circle cx="100" cy="100" r="80" fill="white" opacity="0.1"/>
          <circle cx="300" cy="200" r="120" fill="white" opacity="0.05"/>
          <circle cx="600" cy="400" r="100" fill="white" opacity="0.08"/>
          <circle cx="700" cy="150" r="60" fill="white" opacity="0.12"/>
          <circle cx="150" cy="450" r="90" fill="white" opacity="0.06"/>
          <circle cx="500" cy="80" r="70" fill="white" opacity="0.09"/>
          <circle cx="400" cy="500" r="110" fill="white" opacity="0.04"/>
        </svg>
      </div>

      <div class="relative z-10 flex flex-col justify-center px-12 lg:px-20">
        <!-- Logo -->
        <div class="mb-8">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center">
              <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
            </div>
            <span class="text-2xl font-bold text-white">Novera</span>
          </div>
          <h1 class="text-4xl lg:text-5xl font-bold text-white leading-tight">
            Modern<br/>
            <span class="text-primary-400">Business</span> ERP
          </h1>
          <p class="mt-4 text-lg text-slate-300 max-w-md">
            Manage your entire business — from inventory and production to sales and delivery, for any company.
          </p>
        </div>

        <!-- Features -->
        <div class="space-y-4 mt-8">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-primary-500/20 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <span class="text-slate-300">Track materials → production → QC → shipment</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-primary-500/20 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <span class="text-slate-300">Multi-company workspaces with roles</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-primary-500/20 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <span class="text-slate-300">Real-time inventory across warehouses</span>
          </div>
        </div>

        <div class="mt-12 flex items-center gap-2 text-slate-400 text-sm">
          <span>Works for any company</span>
        </div>
      </div>
    </div>

    <!-- Right side - Login form -->
    <div class="flex-1 flex flex-col justify-center px-6 sm:px-12 lg:px-20 bg-white">
      <div class="mx-auto w-full max-w-sm">
        <!-- Mobile logo -->
        <div class="lg:hidden mb-8">
          <div class="flex items-center gap-2">
            <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
            </div>
            <span class="text-xl font-bold text-gray-900">Novera</span>
          </div>
        </div>

        <div>
          <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
          <p class="mt-2 text-sm text-gray-500">
            Sign in to your ERP dashboard
          </p>
        </div>

        <!-- Error -->
        <div v-if="error" class="mt-6 rounded-lg bg-red-50 border border-red-200 p-4">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>
        </div>

        <!-- Form -->
        <form class="mt-8 space-y-5" @submit.prevent="handleSubmit">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-3"
              placeholder="you@company.com"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              required
              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm px-4 py-3"
              placeholder="Enter your password"
            />
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2">
              <input type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
              <span class="text-sm text-gray-600">Remember me</span>
            </label>
            <span class="text-xs text-gray-400" title="Contact your workspace admin to reset your password">
              Contact admin to reset password
            </span>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center items-center gap-2 rounded-lg bg-primary-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            {{ loading ? 'Signing in...' : 'Sign in' }}
          </button>
        </form>

        <!-- Demo credentials -->
        <div class="mt-6 rounded-lg bg-slate-50 border border-slate-200 p-4">
          <p class="text-xs font-medium text-slate-600 mb-2">Demo Credentials</p>
          <div class="text-xs text-slate-500 space-y-1">
            <p><span class="font-medium">Email:</span> admin@feltandyarn.com</p>
            <p><span class="font-medium">Password:</span> password</p>
          </div>
        </div>

        <p class="mt-8 text-center text-xs text-gray-400">
          Novera — for every company
        </p>
      </div>
    </div>
  </div>
</template>
