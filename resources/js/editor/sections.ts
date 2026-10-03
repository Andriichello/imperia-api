import {keyId, keyKind, PreviewPage} from '@/editor/protocol'

/** Panels of the editor: one per part of the page (and the brand colors of all of them). */
export type Section = 'photos' | 'details' | 'notes' | 'menus' | 'hours' | 'brand' | 'menu' | 'category' | 'dish'

/** Sections of one menu, category or dish (the others are the restaurant's). */
export const ITEM_SECTIONS: Section[] = ['menu', 'category', 'dish']

/** Parts of a dish, which can be picked on its page in the preview (`dish-sizes`). */
export type DishField = 'photos' | 'badge' | 'text' | 'sizes' | 'tags' | 'allergens'

export const DISH_FIELDS: DishField[] = ['photos', 'badge', 'text', 'sizes', 'tags', 'allergens']

/**
 * The part being edited: a section of the restaurant page, or a menu, category or dish.
 * A new menu, category or dish has a negative id till it's saved (see `isNewId()`), and the
 * id of the one it's added to (a new menu, the restaurant's).
 */
export interface Selection {
  section: Section
  id: number | null
  parent?: number | null
  // the part of the dish picked on its page in the preview
  field?: DishField | null
}

/** Whether it's the id of a new menu, category or dish, which isn't saved yet. */
export function isNewId(id: number | null | undefined): boolean {
  return typeof id === 'number' && id < 0
}

/** A link of a panel's breadcrumb: to another panel, or to the page structure (no selection). */
export interface Breadcrumb {
  label: string
  selection: Selection | null
}

/** A tab of a content language, with the number of texts, which are written in it. */
export interface LanguageTab {
  locale: string
  label: string
  isDefault: boolean
  // texts written in the default language, and the ones of them written in this one too
  total: number
  filled: number
}

/**
 * Key of the draft of the part: "notes", "dish:12" (see `ItemKind`).
 */
export function draftKey(selection: Selection): string {
  return ITEM_SECTIONS.includes(selection.section) ? `${selection.section}:${selection.id}` : selection.section
}

/**
 * The part a key of the public page is about (see `editKey()`).
 *
 * @param key
 * @param dishId The dish of the dish page, whose parts ("dish-sizes") are picked
 */
export function selectionOf(key: string, dishId: number | null = null): Selection | null {
  const kind = keyKind(key)

  switch (kind) {
    case 'photos':
    case 'details':
    case 'notes':
    case 'menus':
    case 'hours':
      return {section: kind, id: null}
    // the contacts are edited with the details
    case 'contact':
      return {section: 'details', id: null}
    case 'menu-tabs':
      return {section: 'menus', id: null}
    case 'menu':
    case 'category':
    case 'dish': {
      const id = keyId(key)

      return id ? {section: kind, id} : null
    }
  }

  const field = kind.startsWith('dish-') ? kind.slice(5) as DishField : null

  return field && DISH_FIELDS.includes(field) && dishId ? {section: 'dish', id: dishId, field} : null
}

/**
 * Keys of the parts of the public page, which show what's edited (the first one is scrolled to).
 */
export function keysOf(selection: Selection | null): string[] {
  if (!selection) {
    return []
  }

  switch (selection.section) {
    case 'details':
      return ['details', 'contact']
    case 'menus':
      return ['menus', 'menu-tabs']
    // on every part of every page
    case 'brand':
      return []
    case 'dish':
      return selection.field
        ? [`dish-${selection.field}`, `dish:${selection.id}`]
        : [`dish:${selection.id}`]
    case 'menu':
    case 'category':
      return [`${selection.section}:${selection.id}`]
    default:
      return [selection.section]
  }
}

export function isSameSelection(a: Selection | null, b: Selection | null): boolean {
  return a?.section === b?.section
    && (a?.id ?? null) === (b?.id ?? null)
    && (a?.parent ?? null) === (b?.parent ?? null)
}

/**
 * The selection in the editor's URL (`?select=dish:12`), null for the page structure.
 */
export function selectionParam(selection: Selection | null): string | null {
  return selection ? draftKey(selection) : null
}

/**
 * The selection of the URL's parameter (a new item's parent isn't in it, see the store).
 */
export function parseSelectionParam(value: string | null): Selection | null {
  if (!value) {
    return null
  }

  const [section, id] = value.split(':')

  if (ITEM_SECTIONS.includes(section as Section)) {
    const number = parseInt(id ?? '')

    return Number.isFinite(number) && number !== 0 ? {section: section as Section, id: number} : null
  }

  return ['photos', 'details', 'notes', 'menus', 'hours', 'brand'].includes(section)
    ? {section: section as Section, id: null}
    : null
}

/**
 * The page of the preview in the editor's URL (`?page=menu:3`, `?page=dish:12`), none for the
 * restaurant page.
 */
export function pageParam(page: PreviewPage): string | null {
  if (page.page === 'dish' && page.dishId) {
    return `dish:${page.dishId}`
  }

  return page.page === 'menu' && page.menuId ? `menu:${page.menuId}` : null
}

/**
 * The page of the URL's parameter: its kind and id (the menu of a dish page is found by the store).
 */
export function parsePageParam(value: string | null): { page: 'menu' | 'dish', id: number } | null {
  const [page, id] = (value ?? '').split(':')
  const number = parseInt(id ?? '')

  return (page === 'menu' || page === 'dish') && Number.isFinite(number) && number > 0 ? {page, id: number} : null
}
