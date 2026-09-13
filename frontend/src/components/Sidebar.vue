<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import OrgSwitcher from '@/components/OrgSwitcher.vue'
import {
  HomeIcon,
  CubeIcon,
  ArchiveBoxIcon,
  CogIcon,
  ShoppingBagIcon,
  ChatBubbleLeftRightIcon,
  TruckIcon,
  CurrencyDollarIcon,
  UserGroupIcon,
  ChartBarIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const expandedMenus = ref<string[]>(['Catalog'])

interface NavItem {
  name: string
  icon?: any
  path?: string
  children?: NavItem[]
  permission?: string
  color?: string
}

const navigation: NavItem[] = [
  { name: 'Dashboard', icon: HomeIcon, path: '/', color: 'text-primary-400' },
  {
    name: 'Catalog',
    icon: CubeIcon,
    permission: 'catalog.view',
    color: 'text-violet-400',
    children: [
      { name: 'Products', path: '/catalog/products' },
      { name: 'Categories', path: '/catalog/categories' },
      { name: 'Color Chart', path: '/catalog/color-chart' },
      { name: 'Attributes', path: '/catalog/attributes' },
    ],
  },
  {
    name: 'Inventory',
    icon: ArchiveBoxIcon,
    permission: 'inventory.view',
    color: 'text-amber-400',
    children: [
      { name: 'Raw Materials', path: '/inventory/raw-materials' },
      { name: 'Stock Movements', path: '/inventory/stock-movements' },
      { name: 'Warehouses', path: '/inventory/warehouses' },
    ],
  },
  {
    name: 'Production',
    icon: CogIcon,
    permission: 'production.view',
    color: 'text-emerald-400',
    children: [
      { name: 'Production Orders', path: '/production/orders' },
      { name: 'QC Inspections', path: '/production/qc' },
      { name: 'Artisans', path: '/production/artisans' },
    ],
  },
  {
    name: 'Sales',
    icon: ShoppingBagIcon,
    permission: 'sales.view',
    color: 'text-blue-400',
    children: [
      { name: 'Sales Orders', path: '/sales/orders' },
      { name: 'Wholesale Accounts', path: '/sales/wholesale' },
    ],
  },
  {
    name: 'CRM',
    icon: ChatBubbleLeftRightIcon,
    permission: 'crm.view',
    color: 'text-pink-400',
    children: [
      { name: 'Leads', path: '/crm/leads' },
      { name: 'Quotes', path: '/crm/quotes' },
    ],
  },
  {
    name: 'Procurement',
    icon: TruckIcon,
    permission: 'procurement.view',
    color: 'text-cyan-400',
    children: [
      { name: 'Purchase Orders', path: '/procurement/purchase-orders' },
      { name: 'Suppliers', path: '/procurement/suppliers' },
    ],
  },
  {
    name: 'Logistics',
    icon: TruckIcon,
    permission: 'logistics.view',
    color: 'text-orange-400',
    children: [
      { name: 'Shipments', path: '/logistics/shipments' },
    ],
  },
  {
    name: 'Finance',
    icon: CurrencyDollarIcon,
    permission: 'finance.view',
    color: 'text-green-400',
    children: [
      { name: 'Invoices', path: '/finance/invoices' },
      { name: 'Expenses', path: '/finance/expenses' },
    ],
  },
  {
    name: 'HR',
    icon: UserGroupIcon,
    permission: 'hr.view',
    color: 'text-indigo-400',
    children: [
      { name: 'Artisans', path: '/hr/artisans' },
      { name: 'Payroll Runs', path: '/hr/payroll' },
      { name: 'Piece Rates', path: '/hr/piece-rates' },
    ],
  },
  {
    name: 'Reports',
    icon: ChartBarIcon,
    permission: 'reports.view',
    color: 'text-rose-400',
    children: [
      { name: 'Sales Report', path: '/reports/sales' },
      { name: 'Production Report', path: '/reports/production' },
      { name: 'Inventory Report', path: '/reports/inventory' },
    ],
  },
  {
    name: 'Workspace',
    icon: UserGroupIcon,
    color: 'text-slate-400',
    children: [
      { name: 'Companies', path: '/companies' },
      { name: 'Users', path: '/admin/users' },
      { name: 'Roles', path: '/admin/roles' },
      { name: 'Activity Log', path: '/admin/activity' },
      { name: 'Billing', path: '/settings/billing' },
      { name: 'Settings', path: '/settings' },
    ],
  },
]

const filteredNavigation = computed(() => {
  return navigation.filter(item => {
    if (!item.permission) return true
    return authStore.hasPermission(item.permission)
  })
})

const toggleMenu = (name: string) => {
  const index = expandedMenus.value.indexOf(name)
  if (index === -1) {
    expandedMenus.value.push(name)
  } else {
    expandedMenus.value.splice(index, 1)
  }
}

const isActive = (path: string) => route.path === path
const isExpanded = (name: string) => expandedMenus.value.includes(name)
const isChildActive = (item: NavItem) => {
  return item.children?.some(child => isActive(child.path!))
}

const navigate = (path: string) => {
  router.push(path)
}
</script>

<template>
  <div class="flex flex-col h-full bg-slate-950">
    <div class="flex items-center h-16 flex-shrink-0 px-4">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center shadow-lg shadow-primary-600/30">
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <div>
          <span class="text-sm font-bold text-white tracking-tight">Novera</span>
          <p class="text-[10px] text-slate-500 -mt-0.5">ERP Suite</p>
        </div>
      </div>
    </div>
    <div class="px-3 pb-2">
      <OrgSwitcher />
    </div>

    <!-- Navigation -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-1 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
      <template v-for="item in filteredNavigation" :key="item.name">
        <!-- Menu with children -->
        <div v-if="item.children">
          <button
            @click="toggleMenu(item.name)"
            :class="[
              isChildActive(item)
                ? 'bg-slate-800/50 text-white'
                : 'text-slate-400 hover:bg-slate-800/30 hover:text-slate-200',
              'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group'
            ]"
          >
            <div :class="[
              isChildActive(item) ? item.color : 'text-slate-500 group-hover:' + item.color,
              'transition-colors duration-200'
            ]">
              <component :is="item.icon" class="h-5 w-5" />
            </div>
            <span class="flex-1 text-left">{{ item.name }}</span>
            <ChevronDownIcon
              :class="[
                isExpanded(item.name) ? 'rotate-180' : '',
                'h-4 w-4 text-slate-500 transition-transform duration-200',
              ]"
            />
          </button>

          <!-- Submenu -->
          <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-50"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 max-h-50"
            leave-to-class="opacity-0 max-h-0"
          >
            <div v-if="isExpanded(item.name)" class="overflow-hidden ml-4 mt-1 space-y-0.5 border-l border-slate-700/50 pl-3">
              <button
                v-for="child in item.children"
                :key="child.name"
                @click="navigate(child.path!)"
                :class="[
                  isActive(child.path!)
                    ? 'bg-primary-600/10 text-primary-400 border-primary-500'
                    : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30 border-transparent',
                  'w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 border-l-2 -ml-[13px] pl-4'
                ]"
              >
                {{ child.name }}
              </button>
            </div>
          </Transition>
        </div>

        <!-- Single item -->
        <button
          v-else
          @click="navigate(item.path!)"
          :class="[
            isActive(item.path!)
              ? 'bg-primary-600/10 text-primary-400'
              : 'text-slate-400 hover:bg-slate-800/30 hover:text-slate-200',
            'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200'
          ]"
        >
          <component :is="item.icon" :class="[isActive(item.path!) ? 'text-primary-400' : 'text-slate-500', 'h-5 w-5']" />
          {{ item.name }}
        </button>
      </template>
    </div>

    <!-- Bottom user info -->
    <div class="flex-shrink-0 px-4 py-4 border-t border-slate-800">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-sm font-bold shadow-lg shadow-primary-600/20">
          {{ authStore.user?.name?.charAt(0) || 'U' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-white truncate">{{ authStore.user?.name }}</p>
          <p class="text-xs text-slate-500 truncate">{{ authStore.user?.email }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
