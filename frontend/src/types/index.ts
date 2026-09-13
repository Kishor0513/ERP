export interface Organization {
  id: number
  name: string
  slug: string
  trial_ends_at: string | null
  is_active: boolean
  pivot?: { role: string }
}

export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'manager' | 'staff' | 'artisan'
  permissions: string[]
  organizations?: Organization[]
  current_organization_id?: number | null
  current_organization?: Organization | null
  created_at: string
  updated_at: string
}

export interface AuthState {
  user: User | null
  token: string | null
  loading: boolean
}

export interface Product {
  id: number
  name: string
  sku: string
  description: string
  category_id: number
  category?: Category
  price: number
  cost_price: number
  stock_quantity: number
  low_stock_threshold: number
  status: 'active' | 'inactive'
  variants: ProductVariant[]
  images: ProductImage[]
  created_at: string
  updated_at: string
}

export interface ProductVariant {
  id: number
  product_id: number
  name: string
  sku: string
  price: number
  stock_quantity: number
  attributes: Record<string, string>
  created_at: string
  updated_at: string
}

export interface ProductImage {
  id: number
  product_id: number
  path: string
  alt_text: string
  sort_order: number
  created_at: string
}

export interface Category {
  id: number
  name: string
  slug: string
  parent_id: number | null
  children?: Category[]
  created_at: string
  updated_at: string
}

export interface ColorChart {
  id: number
  name: string
  code: string
  hex_value: string
  created_at: string
  updated_at: string
}

export interface Attribute {
  id: number
  name: string
  type: 'text' | 'number' | 'select' | 'color'
  options: string[]
  created_at: string
  updated_at: string
}

export interface RawMaterial {
  id: number
  name: string
  sku: string
  unit: string
  cost_per_unit: number
  stock_quantity: number
  reorder_point: number
  supplier_id: number | null
  supplier?: Supplier
  created_at: string
  updated_at: string
}

export interface StockMovement {
  id: number
  type: 'in' | 'out' | 'adjustment' | 'transfer'
  raw_material_id: number
  raw_material?: RawMaterial
  quantity: number
  reference_type: string
  reference_id: number
  notes: string
  user_id: number
  user?: User
  created_at: string
}

export interface Warehouse {
  id: number
  name: string
  code: string
  address: string
  city: string
  state: string
  country: string
  created_at: string
  updated_at: string
}

export interface ProductionOrder {
  id: number
  order_number: string
  product_id: number | null
  product?: Product
  quantity: number
  status: 'pending' | 'in_progress' | 'qc' | 'completed' | 'cancelled'
  priority: 'low' | 'medium' | 'high' | 'urgent'
  assigned_artisan_id: number | null
  assigned_artisan?: Artisan
  due_date: string
  started_at: string | null
  completed_at: string | null
  notes: string
  qc_results: QCResult[]
  created_at: string
  updated_at: string
}

export interface Artisan {
  id: number
  user_id: number
  user?: User
  name: string
  skills: string[]
  status: 'active' | 'inactive' | 'on_leave'
  hourly_rate: number
  phone: string
  address: string
  created_at: string
  updated_at: string
}

export interface QCResult {
  id: number
  production_order_id: number
  inspector_id: number
  inspector?: User
  status: 'passed' | 'failed' | 'pending'
  defects: QCDefect[]
  notes: string
  created_at: string
}

export interface QcInspection {
  id: number
  production_order_id: number
  artisan_id: number
  inspector_id?: number | null
  result: 'pass' | 'fail' | 'rework'
  defect_reason?: string | null
  defect_details?: Record<string, any> | null
  notes?: string | null
  inspected_at?: string | null
  created_at: string
  updated_at: string
}

export interface QCDefect {
  type: string
  description: string
  severity: 'minor' | 'major' | 'critical'
}

export interface SalesOrder {
  id: number
  order_number: string
  customer_name: string
  customer_email: string
  customer_phone: string
  channel: 'online' | 'wholesale' | 'retail' | 'marketplace'
  status: 'pending' | 'confirmed' | 'processing' | 'shipped' | 'delivered' | 'cancelled'
  items: SalesOrderItem[]
  subtotal: number
  tax: number
  shipping: number
  total: number
  shipping_address: Address
  billing_address: Address
  notes: string
  created_at: string
  updated_at: string
}

export interface SalesOrderItem {
  id: number
  product_id: number
  product?: Product
  variant_id: number | null
  quantity: number
  unit_price: number
  total: number
}

export interface WholesaleAccount {
  id: number
  company_name: string
  contact_name: string
  email: string
  phone: string
  address: Address
  status: 'pending' | 'approved' | 'rejected' | 'suspended'
  credit_limit: number
  payment_terms: number
  discount_percentage: number
  tax_id: string
  documents: WholesaleDocument[]
  created_at: string
  updated_at: string
}

export interface WholesaleDocument {
  id: number
  name: string
  path: string
  type: string
  created_at: string
}

export interface Lead {
  id: number
  name: string
  email: string
  phone: string
  company: string
  source: string
  status: 'new' | 'contacted' | 'qualified' | 'converted' | 'lost'
  notes: string
  assigned_to: number | null
  assigned_user?: User
  created_at: string
  updated_at: string
}

export interface Quote {
  id: number
  quote_number: string
  lead_id: number | null
  lead?: Lead
  customer_name: string
  customer_email: string
  items: QuoteItem[]
  subtotal: number
  tax: number
  discount: number
  total: number
  status: 'draft' | 'sent' | 'accepted' | 'rejected' | 'expired'
  valid_until: string
  notes: string
  created_at: string
  updated_at: string
}

export interface QuoteItem {
  id: number
  product_id: number
  product?: Product
  description: string
  quantity: number
  unit_price: number
  total: number
}

export interface PurchaseOrder {
  id: number
  po_number: string
  supplier_id: number
  supplier?: Supplier
  items: PurchaseOrderItem[]
  subtotal: number
  tax: number
  shipping: number
  total: number
  status: 'draft' | 'sent' | 'confirmed' | 'received' | 'cancelled'
  expected_delivery: string
  notes: string
  created_at: string
  updated_at: string
}

export interface PurchaseOrderItem {
  id: number
  raw_material_id: number
  raw_material?: RawMaterial
  quantity: number
  unit_price: number
  total: number
}

export interface Supplier {
  id: number
  name: string
  contact_name: string
  email: string
  phone: string
  address: Address
  lead_time_days: number
  payment_terms: number
  rating: number
  created_at: string
  updated_at: string
}

export interface Shipment {
  id: number
  shipment_number: string
  sales_order_id: number
  sales_order?: SalesOrder
  carrier: string
  tracking_number: string
  status: 'pending' | 'dispatched' | 'in_transit' | 'delivered' | 'returned'
  shipped_at: string | null
  delivered_at: string | null
  shipping_address: Address
  customs_documents: CustomsDocument[]
  notes: string
  created_at: string
  updated_at: string
}

export interface CustomsDocument {
  id: number
  type: string
  document_number: string
  path: string
  created_at: string
}

export interface Invoice {
  id: number
  invoice_number: string
  sales_order_id: number
  sales_order?: SalesOrder
  amount: number
  tax: number
  total: number
  status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled'
  due_date: string
  paid_at: string | null
  payments: Payment[]
  created_at: string
  updated_at: string
}

export interface Payment {
  id: number
  invoice_id: number
  amount: number
  method: 'cash' | 'bank_transfer' | 'credit_card' | 'cheque'
  reference: string
  paid_at: string
}

export interface Expense {
  id: number
  category: string
  description: string
  amount: number
  date: string
  receipt_path: string | null
  approved_by: number | null
  status: 'pending' | 'approved' | 'rejected'
  created_at: string
  updated_at: string
}

export interface PayrollRun {
  id: number
  period_start: string
  period_end: string
  status: 'draft' | 'approved' | 'paid'
  total_amount: number
  entries: PayrollEntry[]
  approved_at: string | null
  paid_at: string | null
  created_at: string
  updated_at: string
}

export interface PayrollEntry {
  id: number
  artisan_id: number
  artisan?: Artisan
  base_pay: number
  piece_rate_pay: number
  deductions: number
  net_pay: number
  pieces_produced: number
}

export interface PieceRate {
  id: number
  product_id: number | null
  product?: Product
  operation: string
  rate: number
  unit: string
  created_at: string
  updated_at: string
}

export interface DashboardData {
  sales: {
    total: number
    change: number
    chart: { date: string; amount: number }[]
  }
  production: {
    active_orders: number
    completed_today: number
    pending_qc: number
  }
  inventory: {
    low_stock_items: number
    total_value: number
  }
  recent_orders: SalesOrder[]
  top_products: { product: Product; quantity: number }[]
}

export interface Address {
  street: string
  city: string
  state: string
  postal_code: string
  country: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  links: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}

export interface ApiResponse<T> {
  success: boolean
  data: T
  message?: string
  errors?: Record<string, string[]>
}

export interface LoginRequest {
  email: string
  password: string
}

export interface LoginResponse {
  user: User
  token: string
}

export interface RegisterRequest {
  name: string
  email: string
  password: string
  password_confirmation: string
  organization_name?: string
}
