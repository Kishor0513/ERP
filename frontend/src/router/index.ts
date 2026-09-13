import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/pages/Login.vue'),
    meta: { requiresAuth: false, layout: 'blank' },
  },
  {
    path: '/',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('@/pages/Dashboard.vue'),
        meta: { title: 'Dashboard' },
      },
      // Catalog
      {
        path: 'catalog/products',
        name: 'Products',
        component: () => import('@/pages/catalog/Products.vue'),
        meta: { title: 'Products', permission: 'catalog.view' },
      },
      {
        path: 'catalog/products/:id',
        name: 'ProductDetail',
        component: () => import('@/pages/catalog/ProductDetail.vue'),
        meta: { title: 'Product Detail', permission: 'catalog.view' },
      },
      {
        path: 'catalog/categories',
        name: 'Categories',
        component: () => import('@/pages/catalog/Categories.vue'),
        meta: { title: 'Categories', permission: 'catalog.view' },
      },
      {
        path: 'catalog/color-chart',
        name: 'ColorChart',
        component: () => import('@/pages/catalog/ColorChart.vue'),
        meta: { title: 'Color Chart', permission: 'catalog.view' },
      },
      {
        path: 'catalog/attributes',
        name: 'Attributes',
        component: () => import('@/pages/catalog/Attributes.vue'),
        meta: { title: 'Attributes', permission: 'catalog.view' },
      },
      // Inventory
      {
        path: 'inventory/raw-materials',
        name: 'RawMaterials',
        component: () => import('@/pages/inventory/RawMaterials.vue'),
        meta: { title: 'Raw Materials', permission: 'inventory.view' },
      },
      {
        path: 'inventory/stock-movements',
        name: 'StockMovements',
        component: () => import('@/pages/inventory/StockMovements.vue'),
        meta: { title: 'Stock Movements', permission: 'inventory.view' },
      },
      {
        path: 'inventory/warehouses',
        name: 'Warehouses',
        component: () => import('@/pages/inventory/Warehouses.vue'),
        meta: { title: 'Warehouses', permission: 'inventory.view' },
      },
      // Production
      {
        path: 'production/orders',
        name: 'ProductionOrders',
        component: () => import('@/pages/production/ProductionOrders.vue'),
        meta: { title: 'Production Orders', permission: 'production.view' },
      },
      {
        path: 'production/orders/:id',
        name: 'ProductionDetail',
        component: () => import('@/pages/production/ProductionDetail.vue'),
        meta: { title: 'Production Detail', permission: 'production.view' },
      },
      {
        path: 'production/artisans',
        name: 'Artisans',
        component: () => import('@/pages/production/Artisans.vue'),
        meta: { title: 'Artisans', permission: 'production.view' },
      },
      {
        path: 'production/qc',
        name: 'QcInspections',
        component: () => import('@/pages/production/QcInspections.vue'),
        meta: { title: 'QC Inspections', permission: 'production.view' },
      },
      // Sales
      {
        path: 'sales/orders',
        name: 'SalesOrders',
        component: () => import('@/pages/sales/SalesOrders.vue'),
        meta: { title: 'Sales Orders', permission: 'sales.view' },
      },
      {
        path: 'sales/orders/:id',
        name: 'OrderDetail',
        component: () => import('@/pages/sales/OrderDetail.vue'),
        meta: { title: 'Order Detail', permission: 'sales.view' },
      },
      {
        path: 'sales/wholesale',
        name: 'WholesaleAccounts',
        component: () => import('@/pages/sales/WholesaleAccounts.vue'),
        meta: { title: 'Wholesale Accounts', permission: 'sales.view' },
      },
      {
        path: 'sales/wholesale/:id',
        name: 'WholesaleDetail',
        component: () => import('@/pages/sales/WholesaleDetail.vue'),
        meta: { title: 'Wholesale Detail', permission: 'sales.view' },
      },
      // CRM
      {
        path: 'crm/leads',
        name: 'Leads',
        component: () => import('@/pages/crm/Leads.vue'),
        meta: { title: 'Leads', permission: 'crm.view' },
      },
      {
        path: 'crm/quotes',
        name: 'Quotes',
        component: () => import('@/pages/crm/Quotes.vue'),
        meta: { title: 'Quotes', permission: 'crm.view' },
      },
      {
        path: 'crm/quotes/:id',
        name: 'QuoteBuilder',
        component: () => import('@/pages/crm/QuoteBuilder.vue'),
        meta: { title: 'Quote Builder', permission: 'crm.view' },
      },
      // Procurement
      {
        path: 'procurement/purchase-orders',
        name: 'PurchaseOrders',
        component: () => import('@/pages/procurement/PurchaseOrders.vue'),
        meta: { title: 'Purchase Orders', permission: 'procurement.view' },
      },
      {
        path: 'procurement/suppliers',
        name: 'Suppliers',
        component: () => import('@/pages/procurement/Suppliers.vue'),
        meta: { title: 'Suppliers', permission: 'procurement.view' },
      },
      // Logistics
      {
        path: 'logistics/shipments',
        name: 'Shipments',
        component: () => import('@/pages/logistics/Shipments.vue'),
        meta: { title: 'Shipments', permission: 'logistics.view' },
      },
      {
        path: 'logistics/shipments/:id',
        name: 'ShipmentDetail',
        component: () => import('@/pages/logistics/ShipmentDetail.vue'),
        meta: { title: 'Shipment Detail', permission: 'logistics.view' },
      },
      // Finance
      {
        path: 'finance/invoices',
        name: 'Invoices',
        component: () => import('@/pages/finance/Invoices.vue'),
        meta: { title: 'Invoices', permission: 'finance.view' },
      },
      {
        path: 'finance/expenses',
        name: 'Expenses',
        component: () => import('@/pages/finance/Expenses.vue'),
        meta: { title: 'Expenses', permission: 'finance.view' },
      },
      // HR
      {
        path: 'hr/artisans',
        name: 'HrArtisans',
        component: () => import('@/pages/hr/Artisans.vue'),
        meta: { title: 'Artisans', permission: 'hr.view' },
      },
      {
        path: 'hr/payroll',
        name: 'PayrollRuns',
        component: () => import('@/pages/hr/PayrollRuns.vue'),
        meta: { title: 'Payroll Runs', permission: 'hr.view' },
      },
      {
        path: 'hr/piece-rates',
        name: 'PieceRates',
        component: () => import('@/pages/hr/PieceRates.vue'),
        meta: { title: 'Piece Rates', permission: 'hr.view' },
      },
      // Reports
      {
        path: 'reports/sales',
        name: 'SalesReport',
        component: () => import('@/pages/reports/SalesReport.vue'),
        meta: { title: 'Sales Report', permission: 'reports.view' },
      },
      {
        path: 'reports/production',
        name: 'ProductionReport',
        component: () => import('@/pages/reports/ProductionReport.vue'),
        meta: { title: 'Production Report', permission: 'reports.view' },
      },
      {
        path: 'reports/inventory',
        name: 'InventoryReport',
        component: () => import('@/pages/reports/InventoryReport.vue'),
        meta: { title: 'Inventory Report', permission: 'reports.view' },
      },
      {
        path: 'companies',
        name: 'Companies',
        component: () => import('@/pages/Companies.vue'),
        meta: { title: 'Companies' },
      },
      {
        path: 'admin/users',
        name: 'AdminUsers',
        component: () => import('@/pages/admin/Users.vue'),
        meta: { title: 'Users' },
      },
      {
        path: 'admin/roles',
        name: 'AdminRoles',
        component: () => import('@/pages/admin/Roles.vue'),
        meta: { title: 'Roles' },
      },
      {
        path: 'admin/activity',
        name: 'AdminActivity',
        component: () => import('@/pages/admin/ActivityLog.vue'),
        meta: { title: 'Activity Log' },
      },
      {
        path: 'settings/billing',
        name: 'Billing',
        component: () => import('@/pages/Billing.vue'),
        meta: { title: 'Billing' },
      },
      {
        path: 'settings',
        name: 'Settings',
        component: () => import('@/pages/Settings.vue'),
        meta: { title: 'Settings' },
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth !== false && !authStore.isAuthenticated) {
    return next('/login')
  }

  if (to.path === '/login' && authStore.isAuthenticated) {
    return next('/')
  }

  if (authStore.isAuthenticated && !authStore.user) {
    try {
      await authStore.fetchUser()
    } catch {
      return next('/login')
    }
  }

  if (to.meta.permission && !authStore.hasPermission(to.meta.permission as string)) {
    return next('/')
  }

  next()
})

export default router
