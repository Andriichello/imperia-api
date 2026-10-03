import type {EditorRestaurant, Media} from '@/api'
import {
  orderEditorMenus,
  storeEditorCategory,
  storeEditorDish,
  storeEditorMenu,
  updateEditorCategory,
  updateEditorDish,
  updateEditorMenu,
  updateEditorRestaurant,
  updateEditorRestaurantHours,
  updateEditorRestaurantNotes,
  updateEditorRestaurantPhotos,
} from '@/api'
import {
  DetailsDraft,
  detailsOf,
  detailsPreview,
  detailsRequest,
  HoursDraft,
  hoursOf,
  hoursPreview,
  hoursRequest,
  NoteDraft,
  notesOf,
  notesPreview,
  notesRequest,
  photosPreview,
} from '@/editor/drafts'
import {
  applyCategory,
  applyDish,
  applyMenu,
  applyMenuOrder,
  CategoryDraft,
  categoryOf,
  DishDraft,
  dishOf,
  dishRequest,
  MenuOrder,
  menuOrderOf,
  menusPreview,
  TextsDraft,
  textsOf,
  textsRequest,
} from '@/editor/menuDrafts'
import {BrandColors, brandOf, contrastOnTints, isHex, READABLE} from '@/editor/brand'
import {hoursValid} from '@/editor/hours'
import {findCategory, findDish, findMenu} from '@/editor/find'
import type {PreviewPatch} from '@/editor/protocol'
import {isNewId, Section, Selection} from '@/editor/sections'

/**
 * What the editor keeps drafts of: each section of the restaurant page, the order of the
 * menus, and each menu, category and dish. Every kind knows its saved values (in the shape
 * of its draft), whether a draft can be saved, and how it's saved, so drafts are saved
 * without their panels too ("Save all").
 */

/** Validation errors of a request, by field ("notes.0.text.en"). */
export type ValidationErrors = Record<string, string[]>

/** A draft: the changed values of a part, kept till they're saved or discarded. */
export interface DraftEntry<T = unknown> {
  key: string
  selection: Selection
  values: T
  // of the last save, by field (items of lists by their keys: "notes.note-7.text.en")
  errors: ValidationErrors
  // the last save failed for another reason (no connection, a server error)
  failed: boolean
}

export interface SaveResult {
  // the restaurant afterwards, when the request responds with it
  restaurant?: EditorRestaurant
  // of a new menu, category or dish
  id?: number
}

export interface ItemKind<T> {
  /** The saved values, in the shape of the draft (empty ones of a new item). */
  saved(restaurant: EditorRestaurant, selection: Selection): T

  /** The item is there (a new one: the one it's added to). */
  exists(restaurant: EditorRestaurant, selection: Selection): boolean

  /** The values can be saved as they are (what's wrong with them is shown in its panel). */
  valid(values: T, restaurant: EditorRestaurant, selection: Selection): boolean

  save(values: T, restaurant: EditorRestaurant, selection: Selection): Promise<SaveResult>

  /**
   * Keys of the items of its lists, by the list's field ("notes"): errors of an item
   * ("notes.2.text.en") are kept by its key, it may be moved meanwhile.
   */
  lists?: Record<string, (values: T) => string[]>

  /** The values after the restaurant changed in another way (e.g. a category was archived right away). */
  reconcile?(values: T, restaurant: EditorRestaurant, selection: Selection): T
}

const hasText = (value: Record<string, string>, locale: string) => !!value[locale]?.trim()

const isNumber = (value: string, optional: boolean) => value.trim() === ''
  ? optional
  : /^\d+([.,]\d+)?$/.test(value.trim())

const details: ItemKind<DetailsDraft> = {
  saved: (restaurant) => detailsOf(restaurant),
  exists: () => true,
  valid: (values, restaurant) => hasText(values.name, restaurant.default_locale),
  save: async (values, restaurant) => ({
    restaurant: (await updateEditorRestaurant(restaurant.id, detailsRequest(values))).data.data,
  }),
}

const notes: ItemKind<NoteDraft[]> = {
  saved: (restaurant) => notesOf(restaurant),
  exists: () => true,
  valid: (values, restaurant) => values.every((note) => hasText(note.text, restaurant.default_locale)),
  save: async (values, restaurant) => ({
    restaurant: (await updateEditorRestaurantNotes(restaurant.id, notesRequest(values))).data.data,
  }),
  lists: {notes: (values) => values.map((note) => note.key)},
}

const photos: ItemKind<Media[]> = {
  saved: (restaurant) => restaurant.photos ?? [],
  exists: () => true,
  valid: () => true,
  save: async (values, restaurant) => ({
    restaurant: (await updateEditorRestaurantPhotos(restaurant.id, {
      media: values.map((photo) => ({id: photo.id, is_hidden: photo.is_hidden ?? false})),
    })).data.data,
  }),
}

const hours: ItemKind<HoursDraft> = {
  saved: (restaurant) => hoursOf(restaurant),
  exists: () => true,
  valid: (values) => hoursValid(values),
  save: async (values, restaurant) => ({
    restaurant: (await updateEditorRestaurantHours(restaurant.id, hoursRequest(values))).data.data,
  }),
  lists: {exceptions: (values) => values.exceptions.map((day) => day.key)},
}

const brand: ItemKind<BrandColors> = {
  saved: (restaurant) => ({...brandOf(restaurant)}),
  exists: () => true,
  valid: (values) => isHex(values.primary) && isHex(values.content) && contrastOnTints(values) >= READABLE,
  save: async (values, restaurant) => ({
    restaurant: (await updateEditorRestaurant(restaurant.id, {
      brand_primary: values.primary.toLowerCase(),
      brand_primary_content: values.content.toLowerCase(),
    })).data.data,
  }),
}

const menus: ItemKind<MenuOrder[]> = {
  saved: (restaurant) => menuOrderOf(restaurant.menus),
  exists: () => true,
  valid: () => true,
  save: async (values, restaurant) => ({
    restaurant: (await orderEditorMenus(restaurant.id, {menus: values})).data.data,
  }),
  /**
   * Menus and categories, which are gone (archived, deleted), are left out, new ones (restored,
   * copied) are added. The rest stays as it is in the draft.
   */
  reconcile: (values, restaurant) => {
    const saved = menuOrderOf(restaurant.menus)
    const savedIds = new Set(saved.map((item) => item.id))
    const categories = new Set(saved.flatMap((item) => item.categories))

    const synced = values
      .filter((item) => savedIds.has(item.id))
      .map((item) => ({...item, categories: item.categories.filter((id) => categories.has(id))}))

    const kept = new Set(synced.map((item) => item.id))
    synced.push(...saved.filter((item) => !kept.has(item.id)))

    const placed = new Set(synced.flatMap((item) => item.categories))

    for (const item of saved) {
      const target = synced.find((other) => other.id === item.id)!

      target.categories.push(...item.categories.filter((id) => !placed.has(id)))
    }

    return synced
  },
}

const menu: ItemKind<TextsDraft> = {
  saved: (restaurant, selection) => textsOf(findMenu(restaurant, selection.id), restaurant.supported_locales),
  exists: (restaurant, selection) => isNewId(selection.id) || !!findMenu(restaurant, selection.id),
  valid: (values, restaurant) => hasText(values.title, restaurant.default_locale),
  save: async (values, restaurant, selection) => {
    if (!isNewId(selection.id)) {
      await updateEditorMenu(selection.id!, textsRequest(values))

      return {}
    }

    return {id: (await storeEditorMenu(restaurant.id, textsRequest(values))).data.data.id}
  },
}

/** The menu of a category: its own one, or the one a new one is added to. */
function menuIdOf(restaurant: EditorRestaurant, selection: Selection): number | null {
  return isNewId(selection.id) ? (selection.parent ?? null) : (findCategory(restaurant, selection.id)?.menu_id ?? null)
}

const category: ItemKind<CategoryDraft> = {
  saved: (restaurant, selection) => categoryOf(findCategory(restaurant, selection.id), restaurant.supported_locales),
  exists: (restaurant, selection) => isNewId(selection.id)
    ? !!findMenu(restaurant, selection.parent)
    : !!findCategory(restaurant, selection.id),
  valid: (values, restaurant, selection) => !!findMenu(restaurant, menuIdOf(restaurant, selection))
    && hasText(values.title, restaurant.default_locale),
  save: async (values, restaurant, selection) => {
    if (!isNewId(selection.id)) {
      await updateEditorCategory(selection.id!, {...textsRequest(values), dishes: values.dishes})

      return {}
    }

    return {id: (await storeEditorCategory(selection.parent!, textsRequest(values))).data.data.id}
  },
  /** Dishes changed right away: the draft's order stays, gone ones are left out, new ones are at the end. */
  reconcile: (values, restaurant, selection) => {
    const saved = categoryOf(findCategory(restaurant, selection.id), restaurant.supported_locales).dishes
    const kept = values.dishes.filter((id) => saved.includes(id))

    return {...values, dishes: [...kept, ...saved.filter((id) => !kept.includes(id))]}
  },
}

const dish: ItemKind<DishDraft> = {
  saved: (restaurant, selection) => dishOf(findDish(restaurant, selection.id), restaurant.supported_locales),
  exists: (restaurant, selection) => isNewId(selection.id)
    ? !!findCategory(restaurant, selection.parent)
    : !!findDish(restaurant, selection.id),
  valid: (values, restaurant) => hasText(values.title, restaurant.default_locale)
    && values.sizes.every((size) => isNumber(size.price, false) && isNumber(size.weight, true)
      && isNumber(size.preparation_time, true) && isNumber(size.calories, true))
    // guests see a size of it
    && values.sizes.some((size) => !size.is_hidden),
  save: async (values, restaurant, selection) => {
    if (!isNewId(selection.id)) {
      await updateEditorDish(selection.id!, dishRequest(values, false))

      return {}
    }

    return {id: (await storeEditorDish(selection.parent!, dishRequest(values, true))).data.data.id}
  },
  lists: {sizes: (values) => values.sizes.map((size) => size.key)},
}

export const KINDS: Record<Section, ItemKind<any>> = {
  details,
  notes,
  photos,
  hours,
  brand,
  menus,
  menu,
  category,
  dish,
}

/**
 * Order of saving the drafts: the restaurant's sections, the order of the menus, then the
 * menus, categories and dishes (a new one is added to a saved one).
 */
export const SAVE_ORDER: Section[] = ['details', 'photos', 'notes', 'hours', 'brand', 'menus', 'menu', 'category', 'dish']

/** The drafts in the order they're saved in. */
export function bySaveOrder(drafts: DraftEntry[]): DraftEntry[] {
  return [...drafts].sort((a, b) => SAVE_ORDER.indexOf(a.selection.section) - SAVE_ORDER.indexOf(b.selection.section))
}

/** The values as one string, whatever the order of their keys (to compare them). */
export function canonical(value: unknown): string {
  return JSON.stringify(value, (key, item) => item && typeof item === 'object' && !Array.isArray(item)
    ? Object.fromEntries(Object.keys(item).sort().map((name) => [name, item[name]]))
    : item)
}

export const copy = <T>(value: T): T => JSON.parse(JSON.stringify(value))

export function savedOf<T>(restaurant: EditorRestaurant, selection: Selection): T {
  return KINDS[selection.section].saved(restaurant, selection)
}

/** Whether the draft differs from what's saved. */
export function isChanged(entry: DraftEntry, restaurant: EditorRestaurant): boolean {
  return canonical(entry.values) !== canonical(savedOf(restaurant, entry.selection))
}

/** Errors of items of lists by their keys (in the order the items were sent). */
export function keyedErrors(received: ValidationErrors, entry: DraftEntry, values: unknown): ValidationErrors {
  const lists = Object.entries(KINDS[entry.selection.section].lists ?? {})
    .map(([field, keysOf]) => ({field, keys: keysOf(values)}))

  return Object.fromEntries(Object.entries(received).map(([field, messages]) => {
    for (const list of lists) {
      const match = field.match(new RegExp(`^${list.field}\\.(\\d+)(.*)$`))
      const key = match ? list.keys[parseInt(match[1])] : undefined

      if (match && key !== undefined) {
        return [`${list.field}.${key}${match[2]}`, messages]
      }
    }

    return [field, messages]
  }))
}

/**
 * The public page's data with the drafts (in the preview's language): the sections, which
 * have drafts (or had them, so that their saved values are shown again), and the menus with
 * their dishes, when any of them has a draft.
 *
 * @param restaurant
 * @param drafts
 * @param touched Sections, which have or had drafts
 * @param locale
 */
export function draftsPreview(restaurant: EditorRestaurant, drafts: DraftEntry[], touched: Set<Section>, locale: string): PreviewPatch {
  const fallback = restaurant.default_locale
  const values = <T>(section: Section): T => (drafts.find((entry) => entry.key === section)?.values
    ?? savedOf(restaurant, {section, id: null})) as T

  const patch: PreviewPatch = {}
  const page: PreviewPatch['restaurant'] = {}

  if (touched.has('details')) {
    Object.assign(page, detailsPreview(values('details'), locale, fallback).restaurant)
  }

  if (touched.has('notes')) {
    Object.assign(page, notesPreview(values('notes'), locale, fallback).restaurant)
  }

  if (touched.has('photos')) {
    Object.assign(page, photosPreview(values('photos')).restaurant)
  }

  if (touched.has('hours')) {
    Object.assign(page, hoursPreview(values('hours'), locale, fallback).restaurant)
  }

  if (Object.keys(page).length) {
    patch.restaurant = page
  }

  if (['menus', 'menu', 'category', 'dish'].some((section) => touched.has(section as Section))) {
    let list = restaurant.menus
    const order = drafts.find((entry) => entry.key === 'menus')

    if (order) {
      list = applyMenuOrder(list, order.values as MenuOrder[])
    }

    // categories put their dishes in order before new dishes are added to them
    for (const entry of bySaveOrder(drafts)) {
      const {section, id, parent} = entry.selection

      // a new one is on the page, once it has a name
      if (isNewId(id) && !Object.values((entry.values as TextsDraft).title ?? {}).some((text) => text.trim())) {
        continue
      }

      if (section === 'menu') {
        list = applyMenu(list, id!, entry.values as TextsDraft, restaurant.id)
      } else if (section === 'category') {
        list = applyCategory(list, id!, parent ?? 0, entry.values as CategoryDraft)
      } else if (section === 'dish') {
        list = applyDish(list, id!, parent ?? 0, entry.values as DishDraft)
      }
    }

    Object.assign(patch, menusPreview(list, locale, fallback))
  }

  return patch
}

