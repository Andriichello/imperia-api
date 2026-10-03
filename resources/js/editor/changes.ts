import {Info} from 'luxon'
import type {EditorRestaurant, Media} from '@/api'
import type {DetailsDraft, HoursDraft, NoteDraft} from '@/editor/drafts'
import {WEEKDAYS} from '@/editor/drafts'
import type {CategoryDraft, DishDraft, MenuOrder, SizeDraft, TextsDraft} from '@/editor/menuDrafts'
import {BrandColors, presetOf} from '@/editor/brand'
import {ALLERGENS} from '@/flags'
import {findCategory, findDish, findMenu} from '@/editor/find'
import {DraftEntry, savedOf} from '@/editor/items'
import {isNewId, Section} from '@/editor/sections'
import {languageName, translated, Translations} from '@/editor/translations'
import {priceFormatted, weightUnitFormatted} from '@/helpers'

/**
 * What the drafts change, in one line each ("Price 300 g: 185 → 195 ₴ · Description"), for
 * the list of unsaved items, grouped by menu › category.
 */

type Translate = (key: string, values?: Record<string, unknown> | number, plural?: number) => string

interface Context {
  t: Translate
  restaurant: EditorRestaurant
  // of the editor itself (names of weekdays)
  locale: string
}

export interface DraftGroup {
  key: string
  label: string
  // groups follow the menus and their categories, the restaurant page is the last one
  order: number
}

export interface DraftSummary {
  entry: DraftEntry
  name: string
  summary: string
  group: DraftGroup
  // of the item in its group
  order: number
}

const KEY = 'editor.unsaved.'

const same = (a: unknown, b: unknown) => JSON.stringify(a) === JSON.stringify(b)

/**
 * A translated text's change: "Name", or "Name (Українська)" when only other languages changed.
 */
function textChange(label: string, saved: Translations, draft: Translations, ctx: Context): string | null {
  const changed = Object.keys({...saved, ...draft}).filter((locale) => (saved[locale] ?? '') !== (draft[locale] ?? ''))

  if (!changed.length) {
    return null
  }

  return changed.includes(ctx.restaurant.default_locale)
    ? label
    : `${label} (${changed.map(languageName).join(', ')})`
}

function texts(fields: [string, Translations, Translations][], ctx: Context): string[] {
  return fields
    .map(([field, saved, draft]) => textChange(ctx.t(KEY + field), saved, draft, ctx))
    .filter((part): part is string => !!part)
}

function visibility(saved: boolean, draft: boolean, ctx: Context): string[] {
  return saved === draft ? [] : [ctx.t(KEY + (draft ? 'hidden' : 'shown'))]
}

/** Photos or notes, which were hidden ("2 photos hidden"), shown, or both. */
function visibilityOf(hidden: boolean[], items: 'photos' | 'notes', ctx: Context): string {
  if (hidden.every(Boolean)) {
    return ctx.t(`${KEY}${items}_hidden`, hidden.length)
  }

  return hidden.some(Boolean) ? ctx.t(`${KEY}${items}_visibility`) : ctx.t(`${KEY}${items}_shown`, hidden.length)
}

/** Added, removed, replaced, hidden photos, and their order. */
function photoChanges(saved: Media[], draft: Media[], ctx: Context): string[] {
  const savedIds = saved.map((photo) => photo.id)
  const draftIds = draft.map((photo) => photo.id)
  const added = draftIds.filter((id) => !savedIds.includes(id))
  const removed = savedIds.filter((id) => !draftIds.includes(id))
  const parts: string[] = []

  if (added.length === 1 && removed.length === 1) {
    parts.push(ctx.t(KEY + 'photo_replaced'))
  } else {
    if (added.length) {
      parts.push(ctx.t(KEY + 'photos_added', added.length))
    }

    if (removed.length) {
      parts.push(ctx.t(KEY + 'photos_removed', removed.length))
    }
  }

  const hidden = draft.filter((photo) => {
    const before = saved.find((other) => other.id === photo.id)

    return before && !!before.is_hidden !== !!photo.is_hidden
  })

  if (hidden.length) {
    parts.push(visibilityOf(hidden.map((photo) => !!photo.is_hidden), 'photos', ctx))
  }

  const kept = draftIds.filter((id) => savedIds.includes(id))

  if (!same(kept, savedIds.filter((id) => draftIds.includes(id)))) {
    parts.push(ctx.t(KEY + 'photo_order'))
  }

  return parts
}

/** "300 g", or none for a size without a weight. */
function sizeLabel(size: SizeDraft): string {
  return size.weight.trim() ? `${size.weight.trim()} ${weightUnitFormatted(size.weight_unit)}` : ''
}

function sizeChanges(saved: DishDraft, draft: DishDraft, ctx: Context): string[] {
  const parts: string[] = []
  const before = new Map(saved.sizes.filter((size) => size.id).map((size) => [size.id, size]))
  const kept = draft.sizes.filter((size) => size.id && before.has(size.id))
  const currency = (ctx.restaurant.currency ?? 'uah').toLowerCase()
  const named = (key: string, size: SizeDraft) => sizeLabel(size)
    ? ctx.t(KEY + key, {size: sizeLabel(size)})
    : ctx.t(KEY + key + '_unnamed')

  const prices = kept.filter((size) => before.get(size.id)!.price !== size.price)

  if (prices.length === 1) {
    const [size] = prices
    const values = {
      size: sizeLabel(size),
      from: before.get(size.id)!.price,
      to: priceFormatted(Number(size.price.replace(',', '.')), currency) ?? size.price,
    }

    parts.push(ctx.t(KEY + (values.size ? 'price_of' : 'price_change'), values))
  } else if (prices.length) {
    parts.push(ctx.t(KEY + 'prices', prices.length))
  }

  for (const size of draft.sizes.filter((item) => !item.id)) {
    parts.push(named('size_added', size))
  }

  for (const size of saved.sizes.filter((item) => item.id && !draft.sizes.some((other) => other.id === item.id))) {
    parts.push(named('size_removed', size))
  }

  for (const size of kept.filter((item) => before.get(item.id)!.is_hidden !== item.is_hidden)) {
    parts.push(named(size.is_hidden ? 'size_hidden' : 'size_shown', size))
  }

  const details = (size: SizeDraft) => [size.weight, size.weight_unit, size.preparation_time, size.calories]

  if (kept.some((size) => !same(details(size), details(before.get(size.id)!)))) {
    parts.push(ctx.t(KEY + 'sizes'))
  }

  return parts
}

function dishChanges(saved: DishDraft, draft: DishDraft, ctx: Context): string[] {
  const allergens = (flags: string[]) => flags.filter((flag) => ALLERGENS.includes(flag)).sort()
  const diet = (flags: string[]) => flags.filter((flag) => !ALLERGENS.includes(flag)).sort()

  return [
    ...visibility(saved.is_hidden, draft.is_hidden, ctx),
    ...sizeChanges(saved, draft, ctx),
    ...texts([['name', saved.title, draft.title], ['description', saved.description, draft.description],
      ['badge', saved.badge, draft.badge]], ctx),
    ...photoChanges(saved.photos, draft.photos, ctx),
    ...(same(diet(saved.flags), diet(draft.flags)) ? [] : [ctx.t(KEY + 'diet')]),
    ...(same(allergens(saved.flags), allergens(draft.flags)) ? [] : [ctx.t(KEY + 'allergens')]),
  ]
}

function textsChanges(saved: TextsDraft, draft: TextsDraft, ctx: Context): string[] {
  return [
    ...visibility(saved.is_hidden, draft.is_hidden, ctx),
    ...texts([['name', saved.title, draft.title], ['description', saved.description, draft.description]], ctx),
  ]
}

function categoryChanges(saved: CategoryDraft, draft: CategoryDraft, ctx: Context): string[] {
  return [
    ...textsChanges(saved, draft, ctx),
    ...(same(saved.dishes, draft.dishes) ? [] : [ctx.t(KEY + 'dish_order')]),
  ]
}

function menuOrderChanges(saved: MenuOrder[], draft: MenuOrder[], ctx: Context): string[] {
  const parts: string[] = []
  const name = (id: number, find: typeof findMenu | typeof findCategory) =>
    translated(find(ctx.restaurant, id)?.title, ctx.restaurant.default_locale)

  if (!same(saved.map((item) => item.id), draft.map((item) => item.id))) {
    parts.push(ctx.t(KEY + 'menu_order'))
  }

  for (const item of draft) {
    const before = saved.find((other) => other.id === item.id)

    if (before && before.is_hidden !== item.is_hidden) {
      parts.push(ctx.t(KEY + (item.is_hidden ? 'item_hidden' : 'item_shown'), {name: name(item.id, findMenu)}))
    }
  }

  const menuOf = (order: MenuOrder[], id: number) => order.find((item) => item.categories.includes(id))?.id
  const moved = draft.flatMap((item) => item.categories).filter((id) => menuOf(saved, id) !== menuOf(draft, id))

  if (moved.length === 1) {
    parts.push(ctx.t(KEY + 'category_moved', {
      name: name(moved[0], findCategory),
      menu: name(menuOf(draft, moved[0])!, findMenu),
    }))
  } else if (moved.length) {
    parts.push(ctx.t(KEY + 'categories_moved', moved.length))
  }

  const ordered = draft.some((item) => {
    const before = saved.find((other) => other.id === item.id)

    return before && !same(item.categories.filter((id) => before.categories.includes(id)),
      before.categories.filter((id) => item.categories.includes(id)))
  })

  if (ordered) {
    parts.push(ctx.t(KEY + 'category_order'))
  }

  return parts
}

function noteChanges(saved: NoteDraft[], draft: NoteDraft[], ctx: Context): string[] {
  const parts: string[] = []
  const before = new Map(saved.map((note) => [note.id, note]))
  const kept = draft.filter((note) => note.id && before.has(note.id))
  const added = draft.filter((note) => !note.id).length
  const removed = saved.filter((note) => !draft.some((other) => other.id === note.id)).length
  const edited = kept.filter((note) => !same(note.text, before.get(note.id)!.text)).length
  const hidden = kept.filter((note) => note.is_hidden !== before.get(note.id)!.is_hidden)

  if (added) {
    parts.push(ctx.t(KEY + 'notes_added', added))
  }

  if (removed) {
    parts.push(ctx.t(KEY + 'notes_removed', removed))
  }

  if (edited) {
    parts.push(ctx.t(KEY + 'notes_edited', edited))
  }

  if (hidden.length) {
    parts.push(visibilityOf(hidden.map((note) => note.is_hidden), 'notes', ctx))
  }

  if (!same(kept.map((note) => note.id), saved.filter((note) => kept.some((other) => other.id === note.id)).map((note) => note.id))) {
    parts.push(ctx.t(KEY + 'note_order'))
  }

  return parts
}

function detailsChanges(saved: DetailsDraft, draft: DetailsDraft, ctx: Context): string[] {
  return [
    ...texts([['restaurant_name', saved.name, draft.name]], ctx),
    ...(saved.establishment === draft.establishment ? [] : [ctx.t(KEY + 'type')]),
    ...(saved.phone.trim() === draft.phone.trim() ? [] : [ctx.t(KEY + 'phone')]),
    ...texts([['address', saved.address, draft.address]], ctx),
  ]
}

function hoursChanges(saved: HoursDraft, draft: HoursDraft, ctx: Context): string[] {
  const parts: string[] = []
  const time = (hour: number, minute: number) => `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`

  if (saved.timezone !== draft.timezone) {
    parts.push(ctx.t(KEY + 'time_zone'))
  }

  const days = WEEKDAYS.filter((weekday) => !same(saved.weekdays[weekday], draft.weekdays[weekday]))

  if (days.length === 1) {
    const intervals = draft.weekdays[days[0]]
    const day = Info.weekdays('long', {locale: ctx.locale})[WEEKDAYS.indexOf(days[0])]
    const hours = intervals
      .map((interval) => `${time(interval.beg_hour, interval.beg_minute)}–${time(interval.end_hour, interval.end_minute)}`)
      .join(', ')

    parts.push(intervals.length ? ctx.t(KEY + 'day_hours', {day, hours}) : ctx.t(KEY + 'day_closed', {day}))
  } else if (days.length) {
    parts.push(ctx.t(KEY + 'days_hours', days.length))
  }

  if (!same(saved.exceptions, draft.exceptions)) {
    parts.push(ctx.t(KEY + 'special_days'))
  }

  if (saved.closed !== draft.closed || (draft.closed && (saved.closed_until !== draft.closed_until
    || !same(saved.closed_reason, draft.closed_reason)))) {
    parts.push(ctx.t(KEY + (draft.closed ? 'closed_temporarily' : 'reopened')))
  }

  return parts
}

function brandChanges(draft: BrandColors, ctx: Context): string[] {
  const preset = presetOf(draft)

  return [ctx.t(KEY + 'colors', {name: preset ? ctx.t('editor.brand.presets.' + preset.key) : ctx.t(KEY + 'custom_colors')})]
}

/** What the draft changes ("New dish" for a new one). */
function summaryOf(entry: DraftEntry, ctx: Context): string {
  const section = entry.selection.section

  if (isNewId(entry.selection.id)) {
    return ctx.t(KEY + 'new.' + section)
  }

  const saved = savedOf<any>(ctx.restaurant, entry.selection)
  const draft = entry.values as any

  const parts = {
    details: () => detailsChanges(saved, draft, ctx),
    notes: () => noteChanges(saved, draft, ctx),
    photos: () => photoChanges(saved, draft, ctx),
    hours: () => hoursChanges(saved, draft, ctx),
    brand: () => brandChanges(draft, ctx),
    menus: () => menuOrderChanges(saved, draft, ctx),
    menu: () => textsChanges(saved, draft, ctx),
    category: () => categoryChanges(saved, draft, ctx),
    dish: () => dishChanges(saved, draft, ctx),
  }[section]()

  return parts.join(' · ') || ctx.t(KEY + 'changed')
}

/** Name of the item: its name in the draft, or the saved one ("New dish", when it has none yet). */
function nameOf(entry: DraftEntry, ctx: Context): string {
  const {section} = entry.selection

  if (section === 'menu' || section === 'category' || section === 'dish') {
    const values = entry.values as { title: Translations }
    const saved = savedOf<{ title: Translations }>(ctx.restaurant, entry.selection)

    return translated(values.title, ctx.restaurant.default_locale)
      || translated(saved.title, ctx.restaurant.default_locale)
      || ctx.t(`editor.${section}.new`)
  }

  return ctx.t('editor.sections.' + section)
}

/** The group of the item (menu › category of a dish and of a category), and its place in it. */
function groupOf(entry: DraftEntry, ctx: Context): { group: DraftGroup, order: number } {
  const {section, id, parent} = entry.selection
  const restaurant = ctx.restaurant
  const name = (item: { title: unknown } | null | undefined) =>
    translated(item?.title as Translations, restaurant.default_locale)

  if (section === 'category' || section === 'dish') {
    const categoryId = section === 'dish'
      ? (isNewId(id) ? parent : findDish(restaurant, id)?.category_id)
      : id
    const category = findCategory(restaurant, categoryId)
    const menu = findMenu(restaurant, section === 'category' && isNewId(id) ? parent : category?.menu_id)
    const menuIndex = restaurant.menus.findIndex((item) => item.id === menu?.id)
    const categoryIndex = (menu?.categories ?? []).findIndex((item) => item.id === categoryId)
    const dishIndex = (category?.dishes ?? []).findIndex((item) => item.id === id)
    const categoryName = category ? name(category) : nameOf(entry, ctx)

    return {
      group: {
        key: `category:${categoryId}`,
        label: `${name(menu)} › ${categoryName}`,
        order: (menuIndex + 1) * 1000 + (categoryIndex < 0 ? 999 : categoryIndex),
      },
      // the category first, then its dishes in their order, new ones at the end
      order: section === 'category' ? -1 : (dishIndex < 0 ? 9999 : dishIndex),
    }
  }

  if (section === 'menus' || section === 'menu') {
    const index = restaurant.menus.findIndex((item) => item.id === id)

    return {
      group: {key: 'menus', label: ctx.t('editor.sections.menus'), order: 1_000_000},
      order: section === 'menus' ? -1 : (index < 0 ? 9999 : index),
    }
  }

  const sections: Section[] = ['photos', 'details', 'notes', 'hours', 'brand']

  return {
    group: {key: 'restaurant', label: ctx.t(KEY + 'restaurant_page'), order: 2_000_000},
    order: sections.indexOf(section),
  }
}

export function describeDraft(entry: DraftEntry, ctx: Context): DraftSummary {
  return {
    entry,
    name: nameOf(entry, ctx),
    summary: summaryOf(entry, ctx),
    ...groupOf(entry, ctx),
  }
}
