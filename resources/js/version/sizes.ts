import {ref} from 'vue'
import {canonical} from '@/editor/items'
import {weightUnitFormatted} from '@/helpers'
import {Target, TreeNode, valueOf} from '@/version/model'
import {useVersionStore} from '@/stores/version'

/** Fields of a size, which are edited. */
export const SIZE_FIELDS = ['weight', 'weight_unit', 'price', 'preparation_time', 'calories', 'is_hidden'] as const

export type SizeField = typeof SIZE_FIELDS[number] | 'archived'

/** A size of a dish on the version's page. */
export interface SizeRow {
  key: string
  // added by the version (a new size, a size of a new dish)
  isNew: boolean
  live: Record<string, unknown> | null
  // from the version's date (what's typed, till it's saved)
  values: Record<string, unknown>
  changed: (field: SizeField) => boolean
  set: (field: SizeField, value: unknown, delay?: number) => void
  // the changed fields stay live (a saved size)
  revert: ((fields: SizeField[]) => void) | null
  // the version doesn't add it anymore (a new size)
  remove: (() => void) | null
}

/** "300 g", none without a weight. */
export function weightLabel(values: Record<string, unknown> | null): string {
  const weight = values?.weight

  return weight === null || weight === undefined || weight === ''
    ? ''
    : `${weight} ${weightUnitFormatted(String(values?.weight_unit ?? ''))}`.trim()
}

/** Sizes of the dish's node, from the cheapest one (as they're planned, not as they're typed). */
export function sizeRows(dish: TreeNode): SizeRow[] {
  const store = useVersionStore()

  // a new dish has its sizes as one field
  if (dish.isNew) {
    const target = dish.target as Target
    const sizes = store.shown(target, 'sizes', valueOf(dish.change, 'sizes', [] as Record<string, unknown>[])) ?? []

    return sizes.map((values, index) => ({
      key: `${dish.key}.${index}`,
      isNew: true,
      live: null,
      values,
      changed: () => true,
      set: (field, value, delay) => store.edit(target, 'sizes', sizes.map((item, other) => other === index
        ? {...item, [field]: value}
        : item), delay),
      revert: null,
      remove: sizes.length > 1 ? () => store.edit(target, 'sizes', sizes.filter((_, other) => other !== index), 0) : null,
    }))
  }

  return dish.children.map((child) => {
    const size = child.size!
    const target = size.target as Target
    const values = Object.fromEntries([...SIZE_FIELDS, 'archived'].map((field) => [field,
      store.shown(target, field, size.values[field] ?? null)]))

    return {
      key: size.key,
      isNew: !size.live,
      live: size.live,
      values,
      changed: (field) => !size.live || canonical(values[field] ?? null) !== canonical(size.live[field] ?? null),
      set: (field, value, delay) => store.edit(target, field, value, delay),
      revert: size.live ? (fields) => store.revert(target, fields) : null,
      remove: !size.live && size.change ? () => store.remove(size.change!.id) : null,
    }
  })
}

/** A typed number as the version takes it: none for an empty field. */
export function numberInput(value: string): string | null {
  return value.trim() === '' ? null : value.trim().replace(',', '.')
}

/** The price of a size, which it's sorted by: ones without a price come last. */
function priceOf(row: SizeRow): number {
  const price = row.values.price

  return price === null || price === undefined || price === '' || Number.isNaN(Number(price)) ? Infinity : Number(price)
}

/**
 * Sizes from the cheapest one, which keep their places while a price is typed: they're sorted
 * again, once its field is left (`hold()` on its focus, `release()` on its blur).
 */
export function usePriceOrder() {
  // places of the rows, while a price is typed
  const held = ref<Map<string, number> | null>(null)

  function sorted<T extends SizeRow>(rows: T[]): T[] {
    const places = held.value

    return rows
      .map((row, index) => ({row, index}))
      .sort((a, b) => (places
        ? (places.get(a.row.key) ?? Infinity) - (places.get(b.row.key) ?? Infinity)
        : priceOf(a.row) - priceOf(b.row)) || a.index - b.index)
      .map(({row}) => row)
  }

  /** Keep the rows, as they're shown now, in their places. */
  function hold(rows: SizeRow[]) {
    held.value = new Map(rows.map((row, index) => [row.key, index]))
  }

  function release() {
    held.value = null
  }

  return {sorted, hold, release}
}
