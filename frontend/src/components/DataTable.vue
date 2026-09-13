<script setup lang="ts">
import { ref, computed } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid'
import EmptyState from '@/components/EmptyState.vue'

interface Column {
  key: string
  label: string
  sortable?: boolean
  class?: string
  format?: (value: any, row: any) => string
}

interface Props {
  columns: Column[]
  data: any[]
  loading?: boolean
  totalItems?: number
  perPage?: number
  currentPage?: number
  searchable?: boolean
  searchPlaceholder?: string
  selectable?: boolean
  selectedIds?: number[]
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  totalItems: 0,
  perPage: 15,
  currentPage: 1,
  searchable: true,
  searchPlaceholder: 'Search...',
  selectable: false,
  selectedIds: () => [],
})

const emit = defineEmits<{
  (e: 'update:currentPage', value: number): void
  (e: 'update:perPage', value: number): void
  (e: 'sort', key: string, direction: 'asc' | 'desc'): void
  (e: 'search', query: string): void
  (e: 'toggle-select', id: number): void
  (e: 'toggle-select-all'): void
}>()

const searchQuery = ref('')
const sortKey = ref('')
const sortDirection = ref<'asc' | 'desc'>('asc')

const totalPages = computed(() => Math.ceil(props.totalItems / props.perPage))

const pageNumbers = computed(() => {
  const pages = []
  const start = Math.max(1, props.currentPage - 2)
  const end = Math.min(totalPages.value, props.currentPage + 2)
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

let searchTimeout: ReturnType<typeof setTimeout>
const onSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    emit('search', searchQuery.value)
  }, 300)
}

const onSort = (key: string) => {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortDirection.value = 'asc'
  }
  emit('sort', sortKey.value, sortDirection.value)
}

const goToPage = (page: number) => {
  emit('update:currentPage', page)
}

const getSortIcon = (key: string) => {
  if (sortKey.value !== key) return ''
  return sortDirection.value === 'asc' ? '↑' : '↓'
}
</script>

<template>
  <div class="card">
    <div v-if="searchable" class="p-4 border-b border-slate-200/70 dark:border-slate-800">
      <div class="relative max-w-sm">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
          <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10 18a8 8 0 110-16 8 8 0 010 16z" />
          </svg>
        </div>
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          class="input !pl-10"
          @input="onSearch"
        />
      </div>
    </div>

    <div class="overflow-x-auto scrollbar-thin">
      <table class="min-w-full divide-y divide-slate-200/70 dark:divide-slate-800">
        <thead class="bg-slate-50/80 dark:bg-slate-800/50">
          <tr>
            <th v-if="selectable" class="pl-5 pr-0 py-3 w-10">
              <input type="checkbox" :checked="data.length > 0 && data.every((r) => selectedIds.includes(r.id))" @change="emit('toggle-select-all')" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
            </th>
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              class="px-5 py-3 text-left text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap"
              :class="[column.class, column.sortable && 'cursor-pointer select-none hover:text-slate-700 dark:hover:text-slate-200']"
              @click="column.sortable && onSort(column.key)"
            >
              <div class="flex items-center gap-1">
                {{ column.label }}
                <span v-if="column.sortable" class="text-slate-400">{{ getSortIcon(column.key) }}</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-if="loading">
            <td :colspan="columns.length" class="px-5 py-4">
              <div class="space-y-2.5">
                <div v-for="i in 5" :key="i" class="skeleton h-9" />
              </div>
            </td>
          </tr>
          <tr v-else-if="data.length === 0">
            <td :colspan="columns.length" class="px-6">
              <EmptyState title="No results" hint="Try adjusting your search or create a new record." />
            </td>
          </tr>
          <tr v-else v-for="(row, rowIndex) in data" :key="rowIndex" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
            <td v-if="selectable" class="pl-5 pr-0 py-3.5">
              <input type="checkbox" :checked="selectedIds.includes(row.id)" @change="emit('toggle-select', row.id)" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
            </td>
            <td
              v-for="column in columns"
              :key="column.key"
              class="px-5 py-3.5 whitespace-nowrap text-sm text-slate-700 dark:text-slate-200"
              :class="column.class"
            >
              <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                <span v-if="column.format">{{ column.format(row[column.key], row) }}</span>
                <span v-else>{{ row[column.key] }}</span>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="totalPages > 1" class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
      <div class="flex-1 flex justify-between sm:hidden">
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>
      <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <p class="text-sm text-gray-700 dark:text-gray-300">
          Showing
          <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
          to
          <span class="font-medium">{{ Math.min(currentPage * perPage, totalItems) }}</span>
          of
          <span class="font-medium">{{ totalItems }}</span>
          results
        </p>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <ChevronLeftIcon class="h-5 w-5" />
          </button>
          <button
            v-for="page in pageNumbers"
            :key="page"
            @click="goToPage(page)"
            :class="[
              page === currentPage
                ? 'z-10 bg-primary-50 dark:bg-primary-900 border-primary-500 text-primary-600 dark:text-primary-300'
                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600',
              'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
            ]"
          >
            {{ page }}
          </button>
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <ChevronRightIcon class="h-5 w-5" />
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>
