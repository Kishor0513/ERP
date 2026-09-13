import { ref, computed } from 'vue'

export interface ListParams {
  page: number
  per_page: number
  search: string
  sort: string
  order: 'asc' | 'desc'
  filters?: Record<string, any>
}

export function useList(loader: (p: ListParams) => Promise<void>, opts: { perPage?: number } = {}) {
  const search = ref('')
  const currentPage = ref(1)
  const perPage = ref(opts.perPage || 15)
  const sortKey = ref('')
  const sortDirection = ref<'asc' | 'desc'>('asc')
  const filters = ref<Record<string, any>>({})
  const selected = ref<number[]>([])
  const loading = ref(false)

  const params = computed<ListParams>(() => ({
    page: currentPage.value,
    per_page: perPage.value,
    search: search.value,
    sort: sortKey.value,
    order: sortDirection.value,
    ...filters.value,
  }))

  const load = async () => {
    loading.value = true
    try {
      await loader(params.value)
    } finally {
      loading.value = false
    }
  }

  const onSearch = (q: string) => {
    search.value = q
    currentPage.value = 1
    load()
  }

  const onSort = (key: string, dir: 'asc' | 'desc') => {
    sortKey.value = key
    sortDirection.value = dir
    load()
  }

  const onPage = (p: number) => {
    currentPage.value = p
    load()
  }

  const setFilter = (key: string, value: any) => {
    if (value === '' || value === null || value === undefined) {
      delete filters.value[key]
    } else {
      filters.value[key] = value
    }
    currentPage.value = 1
    load()
  }

  const toggleSelect = (id: number) => {
    const i = selected.value.indexOf(id)
    if (i >= 0) selected.value.splice(i, 1)
    else selected.value.push(id)
  }

  const clearSelection = () => {
    selected.value = []
  }

  const exportCsv = (rows: any[], filename: string) => {
    if (!rows.length) return
    const headers = Object.keys(rows[0])
    const esc = (v: any) => `"${String(v ?? '').replace(/"/g, '""')}"`
    const csv = [headers.join(','), ...rows.map((r) => headers.map((h) => esc(r[h])).join(','))].join('\n')
    const blob = new Blob([csv], { type: 'text/csv' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${filename}-${new Date().toISOString().slice(0, 10)}.csv`
    a.click()
    URL.revokeObjectURL(url)
  }

  return { search, currentPage, perPage, sortKey, sortDirection, filters, selected, loading, params, load, onSearch, onSort, onPage, setFilter, toggleSelect, clearSelection, exportCsv }
}
