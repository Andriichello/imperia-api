import {DateTime} from 'luxon'
import type {EditorVersion, EditorVersionChange} from '@/api'
import {priceFormatted, weightUnitFormatted} from '@/helpers'
import {formatDateTime, formatList, formatRelative} from '@/admin/format'

/**
 * Rows of planned menu changes: a change scheduled on its own is described by what it changes
 * ("Chicken broth, 350 g · 140 ₴ → 150 ₴"), a version by its name and counts.
 */
export type RowIcon = 'price' | 'hide' | 'show' | 'note' | 'archive' | 'new' | 'edit' | 'version'

export type Tone = 'neutral' | 'blue' | 'amber' | 'red'

export interface VersionRow {
  version: EditorVersion
  // the name in action labels
  name: string
  icon: RowIcon
  tone: Tone
  // "Chicken broth, 350 g", then what changes: a text, or the old and the new price
  title: string
  what: string | null
  from: string | null
  to: string | null
  isVersion: boolean
  meta: string
  date: string
  status: string
  statusTone: 'muted' | 'amber' | 'red'
}

type Translate = (key: string, values?: Record<string, unknown> | number, plural?: number) => string

interface Context {
  t: Translate
  locale: string
  currency: string | null
  now?: DateTime
}

const TEXT_FIELDS = ['title', 'description', 'badge', 'text', 'name', 'address']

/** The item's name, with the size of a dish's size: "Chicken broth, 350 g". */
function itemName(change: EditorVersionChange, ctx: Context): string {
  const label = change.label
  const name = label?.name ?? ctx.t('admin.dashboard.versions.unnamed')
  const size = label?.size ? `${label.size.weight} ${weightUnitFormatted(label.size.weight_unit ?? '')}`.trim() : null

  return size ? `${name}, ${size}` : name
}

/** What kind of item it is: "Category in Drinks", "Restaurant page". */
function itemKind(change: EditorVersionChange, ctx: Context): string {
  const path = change.label?.path ?? []
  const key = 'admin.dashboard.versions.kinds.'

  switch (change.target_type) {
    case 'dish-menus':
      return ctx.t(key + 'menu')
    case 'dish-categories':
      return ctx.t(key + 'category', {menu: path[0] ?? ''})
    case 'dishes':
    case 'dish-variants':
      return ctx.t(key + 'dish', {category: path[path.length - 1] ?? ''})
    default:
      return ctx.t(key + 'restaurant')
  }
}

/** A change scheduled on its own. */
function describeChange(change: EditorVersionChange, ctx: Context): Pick<VersionRow, 'icon' | 'what' | 'from' | 'to' | 'meta'> {
  const key = 'admin.dashboard.versions.'
  const fields = Object.keys(change.fields)
  const field = fields.length === 1 ? fields[0] : null
  const value = field ? (change.fields as Record<string, { live: unknown, new: unknown }>)[field] : null
  const isNote = change.target_type === 'restaurant-notes'
  const count = ctx.t(key + 'changes', change.changes_count)
  const price = (amount: unknown) => priceFormatted(Number(amount), (ctx.currency ?? 'uah').toLowerCase()) ?? ''

  if (change.is_new) {
    const what = {dishes: 'new_dish', 'dish-variants': 'new_size'}[change.target_type as string] ?? 'new_note'

    return {icon: 'new', what: ctx.t(key + 'what.' + what), from: null, to: null, meta: `${itemKind(change, ctx)} · ${count}`}
  }

  if (field === 'price' && value) {
    return {icon: 'price', what: null, from: price(value.live), to: price(value.new), meta: `${ctx.t(key + 'kinds.price')} · ${count}`}
  }

  let icon: RowIcon = 'edit'
  let what: string

  if (field === 'is_hidden' && value) {
    icon = isNote ? 'note' : (value.new ? 'hide' : 'show')
    what = ctx.t(key + 'what.' + (value.new ? 'hide' : 'show') + (isNote ? '_note' : ''))
  } else if (field === 'archived' && value) {
    icon = 'archive'
    what = ctx.t(key + 'what.' + (value.new ? 'archive' : 'restore'))
  } else {
    const names = [...new Set(fields.map((name) => ctx.t(key + 'fields.' + name)))]
    what = formatList(names, ctx.locale)
  }

  return {icon, what, from: null, to: null, meta: `${itemKind(change, ctx)} · ${count}`}
}

/** What a version changes: "new dishes, prices and photos, archives Summer terrace". */
function summary(version: EditorVersion, ctx: Context): string {
  const key = 'admin.dashboard.versions.kinds.'
  const changes = version.changes
  const has = (test: (change: EditorVersionChange, fields: string[]) => boolean) => changes
    .some((change) => test(change, Object.keys(change.fields)))
  const kinds: string[] = []

  if (has((change) => change.is_new && change.target_type === 'dishes')) kinds.push(ctx.t(key + 'new_dishes'))
  if (has((change) => change.is_new && change.target_type === 'dish-variants')) kinds.push(ctx.t(key + 'new_sizes'))
  if (has((change) => change.is_new && change.target_type === 'restaurant-notes')) kinds.push(ctx.t(key + 'new_notes'))
  if (has((change, fields) => fields.includes('price'))) kinds.push(ctx.t(key + 'prices'))
  if (has((change, fields) => fields.includes('media'))) kinds.push(ctx.t(key + 'photos'))
  if (has((change, fields) => !change.is_new && fields.some((field) => TEXT_FIELDS.includes(field)))) {
    kinds.push(ctx.t(key + 'texts'))
  }
  if (has((change, fields) => fields.includes('is_hidden'))) kinds.push(ctx.t(key + 'visibility'))

  const archived = changes
    .filter((change) => (change.fields as Record<string, { new: unknown }>).archived?.new === true)
    .map((change) => change.label?.name)
    .filter((name): name is string => !!name)

  const parts = kinds.length ? [formatList(kinds, ctx.locale)] : []

  if (archived.length) {
    parts.push(ctx.t(key + 'archives', {names: formatList(archived.slice(0, 2), ctx.locale)}))
  }

  return parts.join(', ')
}

export function describeVersion(version: EditorVersion, ctx: Context): VersionRow {
  const key = 'admin.dashboard.versions.'
  const now = ctx.now ?? DateTime.now()
  const single = !version.name && version.changes.length === 1 ? version.changes[0] : null
  const by = version.created_by ? ctx.t(key + 'by', {name: version.created_by.name}) : null
  const goesLiveAt = version.goes_live_at ? DateTime.fromISO(version.goes_live_at, {setZone: true}) : null

  const row: VersionRow = {
    version,
    name: single ? itemName(single, ctx) : (version.name ?? ctx.t(key + 'unnamed')),
    icon: 'version',
    tone: 'blue',
    title: single ? itemName(single, ctx) : (version.name ?? ctx.t(key + 'unnamed')),
    what: null,
    from: null,
    to: null,
    isVersion: !single,
    meta: '',
    date: goesLiveAt ? formatDateTime(goesLiveAt, ctx.locale, now) : ctx.t(key + 'not_scheduled'),
    status: goesLiveAt ? formatRelative(goesLiveAt, ctx.locale, now) : '',
    statusTone: 'muted',
  }

  let detail: string

  if (single) {
    const change = describeChange(single, ctx)

    Object.assign(row, {icon: change.icon, what: change.what, from: change.from, to: change.to, tone: 'neutral'})
    detail = change.meta
  } else {
    row.what = ctx.t(key + 'changes_in_items', {
      changes: ctx.t(key + 'changes', version.changes_count),
      items: ctx.t(key + 'items', version.items_count),
    })
    detail = [ctx.t(key + 'version'), summary(version, ctx)].filter(Boolean).join(' · ')
  }

  switch (version.status) {
    case 'inactive':
      row.tone = 'amber'
      row.status = ctx.t(key + 'inactive')
      row.statusTone = 'amber'
      detail = [single ? detail : ctx.t(key + 'version'), ctx.t(key + 'inactive_detail')].join(' · ')
      break
    case 'draft':
      row.status = ctx.t(key + 'draft')
      break
    case 'failed':
      row.tone = 'red'
      row.status = ctx.t(key + 'failed')
      row.statusTone = 'red'
      detail = [detail, version.failure_reason].filter(Boolean).join(' · ')
      break
    case 'applied': {
      // when it went live (applied now, it's earlier than its date)
      const appliedAt = version.applied_at ? DateTime.fromISO(version.applied_at, {setZone: true}) : goesLiveAt

      row.date = appliedAt ? formatDateTime(appliedAt, ctx.locale, now) : row.date
      row.status = ctx.t(key + 'went_live')
      break
    }
  }

  row.meta = [detail, by].filter(Boolean).join(' · ')

  return row
}
