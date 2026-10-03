import type {EditorPutVersionChangeRequest, EditorRestaurant, Media} from '@/api'
import type {DetailsDraft, NoteDraft} from '@/editor/drafts'
import type {CategoryDraft, DishDraft, MenuOrder, SizeDraft, TextsDraft} from '@/editor/menuDrafts'
import type {BrandColors} from '@/editor/brand'
import {canonical, copy, DraftEntry, savedOf} from '@/editor/items'
import {isNewId} from '@/editor/sections'
import type {Translations} from '@/editor/translations'

/**
 * Drafts as changes of a scheduled version (see `VersionChangeRules`): what goes live at its
 * date instead of being saved now. What a version can't change (working hours, the order of
 * menus and dishes, deleted notes, new menus and categories) stays in the draft.
 */

export type VersionChange = EditorPutVersionChangeRequest

export interface Scheduling {
  changes: VersionChange[]
  // the draft without what's scheduled, none when nothing stays
  rest: unknown | null
}

type Fields = Record<string, unknown>

const same = (a: unknown, b: unknown) => canonical(a) === canonical(b)

/** Texts of every language, empty ones are null. */
function texts(value: Translations): Record<string, string | null> {
  return Object.fromEntries(Object.entries(value).map(([locale, text]) => [locale, text.trim() || null]))
}

/** A number of a field, null when it's empty. */
function numberOf(value: string): number | null {
  return value.trim() === '' ? null : Number(value.replace(',', '.'))
}

/** The fields, which differ from the saved ones (texts compared as they're saved). */
function changedFields(saved: Fields, draft: Fields, kinds: Record<string, (value: any) => unknown>): Fields {
  const fields: Fields = {}

  for (const [field, convert] of Object.entries(kinds)) {
    const value = convert(draft[field])

    if (!same(convert(saved[field]), value)) {
      fields[field] = value
    }
  }

  return fields
}

const media = (photos: Media[]) => photos.map((photo) => ({id: photo.id, is_hidden: !!photo.is_hidden}))
const flags = (values: string[]) => [...values].sort()
const asIs = (value: unknown) => value

/** Values of a size, as a version takes them. */
function sizeValues(size: SizeDraft): Fields {
  const weight = numberOf(size.weight)

  return {
    price: numberOf(size.price),
    weight,
    weight_unit: weight === null ? null : size.weight_unit,
    calories: numberOf(size.calories),
    preparation_time: numberOf(size.preparation_time),
    is_hidden: size.is_hidden,
  }
}

/** One change of the item, none when nothing of it changes. */
function change(target_type: VersionChange['target_type'], target_id: number, fields: Fields): VersionChange[] {
  return Object.keys(fields).length ? [{target_type, target_id, fields} as VersionChange] : []
}

function details(draft: DetailsDraft, saved: DetailsDraft, restaurant: EditorRestaurant): Scheduling {
  return {
    changes: change('restaurants', restaurant.id, changedFields(saved as unknown as Fields, draft as unknown as Fields, {
      name: texts,
      address: texts,
      establishment: asIs,
      phone: (value: string) => value.trim() || null,
    })),
    rest: null,
  }
}

function brand(draft: BrandColors, saved: BrandColors, restaurant: EditorRestaurant): Scheduling {
  const fields = same(draft, saved) ? {} : {
    brand_primary: draft.primary.toLowerCase(),
    brand_primary_content: draft.content.toLowerCase(),
  }

  return {changes: change('restaurants', restaurant.id, fields), rest: null}
}

function photos(draft: Media[], saved: Media[], restaurant: EditorRestaurant): Scheduling {
  const fields = same(media(draft), media(saved)) ? {} : {media: media(draft)}

  return {changes: change('restaurants', restaurant.id, fields), rest: null}
}

/** Edited, hidden and new notes; deleted ones and the order stay in the draft. */
function notes(draft: NoteDraft[], saved: NoteDraft[], restaurant: EditorRestaurant): Scheduling {
  const changes: VersionChange[] = []
  const before = new Map(saved.map((note) => [note.id, note]))

  for (const note of draft) {
    const old = note.id ? before.get(note.id) : null

    if (old) {
      changes.push(...change('restaurant-notes', note.id!, changedFields(old as unknown as Fields, note as unknown as Fields, {
        text: texts,
        is_hidden: asIs,
      })))
    } else if (!note.id) {
      changes.push({
        target_type: 'restaurant-notes',
        target_id: null,
        parent_id: restaurant.id,
        fields: {text: texts(note.text), is_hidden: note.is_hidden},
      } as VersionChange)
    }
  }

  // the saved notes, which are kept, in the draft's order
  const rest = draft.filter((note) => note.id && before.has(note.id)).map((note) => copy(before.get(note.id)!))

  return {changes, rest: same(rest, saved) ? null : rest}
}

/** Menus hidden or shown; their order and categories moved to other menus stay in the draft. */
function menuOrder(draft: MenuOrder[], saved: MenuOrder[]): Scheduling {
  const before = new Map(saved.map((item) => [item.id, item]))
  const changes = draft.flatMap((item) => {
    const old = before.get(item.id)

    return old && old.is_hidden !== item.is_hidden ? change('dish-menus', item.id, {is_hidden: item.is_hidden}) : []
  })

  const rest = draft.map((item) => ({...copy(item), is_hidden: before.get(item.id)?.is_hidden ?? item.is_hidden}))

  return {changes, rest: same(rest, saved) ? null : rest}
}

function textsOfItem(draft: TextsDraft, saved: TextsDraft): Fields {
  return changedFields(saved as unknown as Fields, draft as unknown as Fields, {
    title: texts,
    description: texts,
    is_hidden: asIs,
  })
}

/** A category's texts; the order of its dishes stays in the draft. */
function category(draft: CategoryDraft, saved: CategoryDraft, id: number): Scheduling {
  const rest = {...copy(saved), dishes: [...draft.dishes]}

  return {
    changes: change('dish-categories', id, textsOfItem(draft, saved)),
    rest: same(rest, saved) ? null : rest,
  }
}

/** A dish and its sizes: changed ones, new ones and deleted ones (archived when it goes live). */
function dish(draft: DishDraft, saved: DishDraft, id: number): Scheduling {
  const changes = change('dishes', id, changedFields(saved as unknown as Fields, draft as unknown as Fields, {
    title: texts,
    description: texts,
    badge: texts,
    is_hidden: asIs,
    flags,
    photos: media,
  }))

  // the photos are the dish's `media`
  for (const item of changes) {
    if (item.fields && 'photos' in item.fields) {
      const {photos: value, ...fields} = item.fields as Fields

      item.fields = {...fields, media: value}
    }
  }

  const before = new Map(saved.sizes.filter((size) => size.id).map((size) => [size.id, size]))

  for (const size of draft.sizes) {
    const old = size.id ? before.get(size.id) : null

    if (old) {
      const values = sizeValues(size)
      const oldValues = sizeValues(old)
      const fields = Object.fromEntries(Object.entries(values).filter(([field, value]) => !same(oldValues[field], value)))

      changes.push(...change('dish-variants', size.id!, fields))
    } else if (!size.id) {
      changes.push({target_type: 'dish-variants', target_id: null, parent_id: id, fields: sizeValues(size)} as VersionChange)
    }
  }

  for (const size of saved.sizes.filter((item) => item.id && !draft.sizes.some((other) => other.id === item.id))) {
    changes.push(...change('dish-variants', size.id!, {archived: true}))
  }

  return {changes, rest: null}
}

/** A new dish in its category, with its sizes. */
function newDish(draft: DishDraft, categoryId: number): Scheduling {
  return {
    changes: [{
      target_type: 'dishes',
      target_id: null,
      parent_id: categoryId,
      fields: {
        title: texts(draft.title),
        description: texts(draft.description),
        badge: texts(draft.badge),
        is_hidden: draft.is_hidden,
        flags: flags(draft.flags),
        media: media(draft.photos),
        sizes: draft.sizes.map(sizeValues),
      },
    } as VersionChange],
    rest: null,
  }
}

/**
 * The changes of the draft for a version, and what stays in the draft. Nothing is scheduled of
 * a draft, which a version can't take (no changes, the draft stays as it is).
 */
export function schedulingOf(entry: DraftEntry, restaurant: EditorRestaurant): Scheduling {
  const {section, id, parent} = entry.selection
  const values = entry.values as any
  const saved = savedOf<any>(restaurant, entry.selection)
  const keep: Scheduling = {changes: [], rest: entry.values}

  // a new menu or category can't be added by a version
  if (isNewId(id)) {
    return section === 'dish' && parent ? newDish(values, parent) : keep
  }

  switch (section) {
    case 'details':
      return details(values, saved, restaurant)
    case 'brand':
      return brand(values, saved, restaurant)
    case 'photos':
      return photos(values, saved, restaurant)
    case 'notes':
      return notes(values, saved, restaurant)
    case 'menus':
      return menuOrder(values, saved)
    case 'menu':
      return {changes: change('dish-menus', id!, textsOfItem(values, saved)), rest: null}
    case 'category':
      return category(values, saved, id!)
    case 'dish':
      return dish(values, saved, id!)
    default:
      // working hours
      return keep
  }
}
