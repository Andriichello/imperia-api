import type {Dish, DishMenu, Restaurant} from '@/api'

/**
 * Messages between the editor and the public page in its preview (a same-origin iframe).
 * Both sides accept them only from each other: the same origin, and the frame or its parent.
 */

/** Select: clicks pick a part of the page to edit. Browse: the page works like for guests. */
export type PreviewMode = 'select' | 'browse'

/** Page shown in the preview: the restaurant page, one of its menus, or a dish's page on it. */
export interface PreviewPage {
  page: 'restaurant' | 'menu' | 'dish'
  menuId: number | null
  dishId?: number | null
}

/**
 * Unsaved changes shown in the preview: values of the public page's data (in its language),
 * which replace the loaded ones.
 */
export interface PreviewPatch {
  restaurant?: Partial<Restaurant>
  // the menus guests see, with their categories, in their order
  menus?: DishMenu[]
  // the dishes guests see, in their order
  products?: Dish[]
}

/** Brand colors of the public page: the primary one and the one of text on its tints. */
export interface PreviewBrand {
  primary: string
  content: string
}

/** Attribute of the parts of the public page, which can be selected (see `editKey()`). */
export const EDIT_KEY_ATTRIBUTE = 'data-edit-key'

/** Editor → preview. */
export type EditorMessage =
  // names of the parts of the page for the outline chips, by kind ("category" for "category:12"),
  // and the chips of hovered and unsaved parts ("{label} · click to edit")
  | { type: 'editor:labels', labels: Record<string, string>, hover: string, unsaved: string }
  | { type: 'editor:mode', mode: PreviewMode }
  | { type: 'editor:alt', held: boolean }
  // the part being edited (several keys: e.g. the restaurant's name and its contacts)
  | { type: 'editor:select', keys: string[], label: string | null }
  // a part to outline as hovered, while its row in the page structure is
  | { type: 'editor:hover', keys: string[] }
  // parts with unsaved changes (drafts)
  | { type: 'editor:unsaved', keys: string[] }
  // scroll to the first of the parts, which is on the page, or open the page of the first one
  | { type: 'editor:scrollTo', keys: string[] }
  | { type: 'editor:navigate', page: PreviewPage }
  | { type: 'editor:locale', locale: string }
  | { type: 'editor:draft', patch: PreviewPatch }
  | { type: 'editor:brand', brand: PreviewBrand }

/** Preview → editor. */
export type PreviewMessage =
  // the page loaded (in this language: it may switch it itself, with its language drawer)
  | { type: 'editor:ready', page: PreviewPage, locale: string }
  | { type: 'editor:select', key: string }
  | { type: 'editor:hover', key: string | null }
  | { type: 'editor:navigate', page: PreviewPage }
  | { type: 'editor:alt', held: boolean }
  | { type: 'editor:escape' }

/**
 * Whether the data of a message event is one of these messages.
 */
export function isBridgeMessage<T extends EditorMessage | PreviewMessage>(data: unknown): data is T {
  return typeof data === 'object'
    && data !== null
    && typeof (data as { type?: unknown }).type === 'string'
    && (data as { type: string }).type.startsWith('editor:')
}

/**
 * Kind of the part of the page: "category" for "category:12", "notes" for "notes".
 */
export function keyKind(key: string): string {
  return key.split(':')[0]
}

/**
 * Id of the item of the part: 12 for "category:12", null for "notes".
 */
export function keyId(key: string): number | null {
  const id = parseInt(key.split(':')[1] ?? '')

  return Number.isFinite(id) ? id : null
}
