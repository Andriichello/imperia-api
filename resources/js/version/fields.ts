import type {EditorVersionChange} from '@/api'
import {canonical} from '@/editor/items'
import {t} from '@/i18n/utils'
import {Target, valueOf} from '@/version/model'
import {useVersionStore} from '@/stores/version'

/** A field of an item on the version's page: its live value, the shown one, and how to change it. */
export interface VersionField<T = unknown> {
  live: T
  // from the version's date (what's typed, till it's saved)
  value: T
  changed: boolean
  conflict: string | null
  error: string | null
  set: (value: T, delay?: number) => void
  revert: () => void
}

/**
 * Fields of the item: its live values, and its change in the version (a new item's fields are
 * all new, they aren't reverted one by one).
 */
export function fieldsFor(target: Target, live: Record<string, unknown> | null, change: EditorVersionChange | null) {
  const store = useVersionStore()
  const isNew = !!change?.is_new || target.id === null

  return <T = unknown>(name: string): VersionField<T> => {
    const liveValue = (live?.[name] ?? null) as T
    const value = store.shown(target, name, valueOf(change, name, liveValue))
    const conflicts = (change?.conflicts?.fields ?? {}) as Record<string, unknown>

    return {
      live: liveValue,
      value,
      changed: isNew || canonical(value) !== canonical(liveValue),
      conflict: name in conflicts ? t('admin.version.conflict') : null,
      error: store.error(target, name),
      set: (next: T, delay?: number) => store.edit(target, name, next, delay),
      revert: () => store.revert(target, [name]),
    }
  }
}
