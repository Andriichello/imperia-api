import {keyId, keyKind} from '@/editor/protocol'

/** Panels of the editor: one per part of the page (and the brand colors of all of them). */
export type Section = 'photos' | 'details' | 'notes' | 'menus' | 'hours' | 'brand' | 'menu' | 'category' | 'dish'

/** The part being edited: a section of the restaurant page, or a menu, category or dish. */
export interface Selection {
  section: Section
  id: number | null
}

/** A link of a panel's breadcrumb: to another panel, or to the page structure (no selection). */
export interface Breadcrumb {
  label: string
  selection: Selection | null
}

/** A tab of a content language, with the number of texts, which aren't translated to it. */
export interface LanguageTab {
  locale: string
  label: string
  isDefault: boolean
  // null: not counted
  missing: number | null
}

/**
 * The part a key of the public page is about (see `editKey()`).
 */
export function selectionOf(key: string): Selection | null {
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

  return null
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
    case 'menu':
    case 'category':
    case 'dish':
      return [`${selection.section}:${selection.id}`]
    default:
      return [selection.section]
  }
}

export function isSameSelection(a: Selection | null, b: Selection | null): boolean {
  return a?.section === b?.section && (a?.id ?? null) === (b?.id ?? null)
}
