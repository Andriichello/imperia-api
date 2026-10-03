import {DateTime} from 'luxon'
import type {EditorRestaurant, EditorVersion, EditorVersionChange} from '@/api'
import {findDish} from '@/editor/find'
import type {Selection} from '@/editor/sections'
import {priceFormatted, weightUnitFormatted} from '@/helpers'

/**
 * Changes of an item, which are scheduled in versions: its panel tells about them.
 */

export interface PlannedChange {
  version: EditorVersion
  change: EditorVersionChange
  goesLiveAt: DateTime
}

type Translate = (key: string, values?: Record<string, unknown> | number, plural?: number) => string

const KEY = 'editor.planned.'

/** Scheduled changes of the menu, category or dish (a dish's sizes too), from the first one. */
export function plannedFor(versions: EditorVersion[], selection: Selection, restaurant: EditorRestaurant): PlannedChange[] {
  const {section, id} = selection
  const dish = section === 'dish' ? findDish(restaurant, id) : null
  const sizes = new Set([...(dish?.sizes ?? []), ...(dish?.archived_sizes ?? [])].map((size) => size.id))

  const isOf = (change: EditorVersionChange) => {
    switch (section) {
      case 'menu':
        return change.target_type === 'dish-menus' && change.target_id === id
      case 'category':
        return change.target_type === 'dish-categories' && change.target_id === id
      case 'dish':
        return (change.target_type === 'dishes' && change.target_id === id)
          || (change.target_type === 'dish-variants'
            && ((change.target_id !== null && sizes.has(change.target_id)) || (change.is_new && change.parent_id === id)))
      default:
        return false
    }
  }

  return versions
    .filter((version) => version.status === 'scheduled' && version.goes_live_at)
    .flatMap((version) => version.changes.filter(isOf).map((change) => ({
      version,
      change,
      goesLiveAt: DateTime.fromISO(version.goes_live_at!, {setZone: true}),
    })))
    .sort((a, b) => a.goesLiveAt.toMillis() - b.goesLiveAt.toMillis())
}

/** What the change does: "archive size 450 g", "price of 300 g: 185 → 195 ₴", "new name". */
export function describePlanned(change: EditorVersionChange, t: Translate, currency: string): string {
  const fields = change.fields as Record<string, { live: unknown, new: unknown }>
  const size = change.label?.size
    ? `${change.label.size.weight ?? ''} ${weightUnitFormatted(change.label.size.weight_unit ?? '')}`.trim()
    : ''
  const named = (key: string) => size ? t(KEY + key, {size}) : t(KEY + key + '_unnamed')
  const price = (value: unknown) => priceFormatted(Number(value), currency.toLowerCase()) ?? ''

  if (change.target_type === 'dish-variants') {
    if (change.is_new) {
      return named('new_size')
    }

    if (fields.archived?.new) {
      return named('archive_size')
    }

    if (fields.price) {
      return t(KEY + (size ? 'price_of' : 'price'), {size, from: fields.price.live, to: price(fields.price.new)})
    }

    if (fields.is_hidden) {
      return named(fields.is_hidden.new ? 'hide_size' : 'show_size')
    }

    return named('change_size')
  }

  if (fields.archived?.new) {
    return t(KEY + 'archive')
  }

  if (fields.is_hidden && Object.keys(fields).length === 1) {
    return t(KEY + (fields.is_hidden.new ? 'hide' : 'show'))
  }

  return t(KEY + 'change', Object.keys(fields).length)
}
