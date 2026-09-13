import { describe, expect, it } from 'vitest'
import { parsePaginated } from './paginated'

describe('parsePaginated', () => {
  it('parses a plain array payload', () => {
    const response = { data: { data: [{ id: 1 }], meta: { total: 1 } } }
    expect(parsePaginated(response)).toEqual({ items: [{ id: 1 }], total: 1 })
  })

  it('falls back to array length without meta', () => {
    const response = { data: { data: [{ id: 1 }, { id: 2 }] } }
    expect(parsePaginated(response)).toEqual({ items: [{ id: 1 }, { id: 2 }], total: 2 })
  })

  it('returns empty result for missing payload', () => {
    expect(parsePaginated({})).toEqual({ items: [], total: 0 })
  })
})
