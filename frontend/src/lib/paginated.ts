import type { PaginatedResponse } from '@/types'

export function parsePaginated<T>(response: any): { items: T[]; total: number } {
  const body = response?.data ?? {}
  const payload = body.data
  if (Array.isArray(payload)) {
    return { items: payload as T[], total: body.meta?.total ?? payload.length }
  }
  const inner = payload as PaginatedResponse<T>
  const items = inner?.data ?? []
  return { items, total: inner?.meta?.total ?? items.length }
}
