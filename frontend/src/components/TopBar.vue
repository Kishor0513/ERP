<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useThemeStore } from '@/stores/theme'
import { useOrganizationStore } from '@/stores/organization'
import {
  Bars3Icon,
  BellIcon,
  MagnifyingGlassIcon,
  SunIcon,
  MoonIcon,
  ArrowRightOnRectangleIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const themeStore = useThemeStore()
const orgStore = useOrganizationStore()

onMounted(async () => {
  try { await orgStore.fetchOrganizations() } catch {}
})

const emit = defineEmits<{
  (e: 'toggle-sidebar'): void
}>()

const showUserMenu = ref(false)
const searchQuery = ref('')
</script>

<template>
  <div class="sticky top-0 z-30 flex h-16 flex-shrink-0 bg-white/85 dark:bg-slate-900/85 backdrop-blur-xl border-b border-slate-200/70 dark:border-slate-800">
    <button
      @click="emit('toggle-sidebar')"
      class="px-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 focus:outline-none lg:hidden"
    >
      <Bars3Icon class="h-6 w-6" />
    </button>

    <div class="flex flex-1 items-center justify-between gap-3 px-4 lg:px-6">
      <div class="flex flex-1 items-center gap-3 min-w-0">
        <span class="hidden md:inline-flex items-center gap-1.5 rounded-full bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 px-3 py-1 text-xs font-semibold ring-1 ring-inset ring-primary-600/20 truncate max-w-56">
          {{ orgStore.organizations.find((o) => o.id === orgStore.currentId)?.name || authStore.user?.current_organization?.name || 'Novera' }}
        </span>
        <div class="w-full max-w-md hidden sm:block">
          <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <MagnifyingGlassIcon class="h-4 w-4 text-slate-400" />
            </div>
            <input
              v-model="searchQuery"
              class="input !pl-10 !bg-slate-50 dark:!bg-slate-800/60"
              placeholder="Search products, orders, customers..."
              type="search"
            />
          </div>
        </div>
      </div>

      <!-- Right actions -->
      <div class="ml-4 flex items-center gap-2">
        <!-- Theme toggle -->
        <button
          @click="themeStore.toggleTheme()"
          class="rounded-xl p-2 text-gray-400 hover:text-gray-600 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
        >
          <SunIcon v-if="themeStore.isDark" class="h-5 w-5" />
          <MoonIcon v-else class="h-5 w-5" />
        </button>

        <!-- Notifications -->
        <button
          class="relative rounded-xl p-2 text-gray-400 hover:text-gray-600 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
        >
          <BellIcon class="h-5 w-5" />
          <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>

        <!-- Divider -->
        <div class="w-px h-6 bg-gray-200 dark:bg-slate-700 mx-1"></div>

        <!-- User menu -->
        <div class="relative">
          <button
            @click="showUserMenu = !showUserMenu"
            class="flex items-center gap-2 rounded-xl p-1.5 pr-3 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
          >
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-sm font-bold shadow-sm">
              {{ authStore.user?.name?.charAt(0) || 'U' }}
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-slate-300 hidden sm:block">
              {{ authStore.user?.name }}
            </span>
            <ChevronDownIcon class="h-4 w-4 text-gray-400 hidden sm:block" />
          </button>

          <!-- Dropdown -->
          <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div
              v-if="showUserMenu"
              class="absolute right-0 mt-2 w-56 rounded-xl shadow-xl py-1 bg-white dark:bg-slate-800 ring-1 ring-black/5 border border-gray-100 dark:border-slate-700"
            >
              <div class="px-4 py-3 border-b border-gray-100 dark:border-slate-700">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ authStore.user?.name }}</p>
                <p class="text-xs text-gray-500 dark:text-slate-400">{{ authStore.user?.email }}</p>
              </div>
              <router-link
                to="/settings"
                class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50"
                @click="showUserMenu = false"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Your Profile
              </router-link>
              <button
                @click="authStore.logout()"
                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 border-t border-gray-100 dark:border-slate-700"
              >
                <ArrowRightOnRectangleIcon class="h-4 w-4" />
                Sign out
              </button>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </div>
</template>
