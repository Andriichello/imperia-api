import {defineStore} from 'pinia'
import type {EditorCategory, EditorDish, EditorMenu, EditorRestaurant, EditorRestaurantItem} from '@/api'
import {getEditorRestaurant} from '@/api'
import type {PreviewBrand, PreviewMode, PreviewPage, PreviewPatch} from '@/editor/protocol'
import {isSameSelection, keysOf, Selection, selectionOf} from '@/editor/sections'
import {brandOf, BrandColors} from '@/editor/brand'
import {t} from '@/i18n/utils'

export interface EditorUser {
  name: string
  email: string
}

/** Pages of the admin (see `AdminPageController::signedIn()`). */
export interface EditorUrls {
  dashboard: string
  editor: string
  logout: string
  // the admin panel, for the ones, who can open it
  panel: string | null
  versions: string
}

/** A message at the bottom of the editor, with an action (e.g. Undo). */
export interface Toast {
  id: number
  message: string
  action?: { label: string, run: () => unknown }
}

/** Answers to a question: its button, the other one (when there's one), or Cancel. */
export type Answer = 'confirm' | 'alternative' | 'cancel'

/** A question, which has to be answered before going on (e.g. before deleting). */
export interface Confirmation {
  title: string
  message: string
  confirm: string
  // a second choice (e.g. Discard next to Save)
  alternative?: string
  danger?: boolean
  resolve: (answer: Answer) => void
}

/** What the open panel does with its changes, when it's left. */
export interface PanelGuard {
  save: () => Promise<boolean>
  discard: () => void
  canSave: () => boolean
}

// the open panel's (functions only, so it isn't in the store's state)
let panelGuard: PanelGuard | null = null

/** How long a toast stays. */
const TOAST_MS = 6000

let toasts = 0

interface EditorState {
  // language of the editor itself
  locale: string
  restaurant: EditorRestaurant | null
  // ones the user can edit
  restaurants: EditorRestaurantItem[]
  user: EditorUser | null
  urls: EditorUrls | null
  // the part being edited, none for the page structure
  selection: Selection | null
  // the part hovered in the preview (its key) and in the page structure
  previewHover: string | null
  treeHover: Selection | null
  mode: PreviewMode
  // Alt (Option) is held: Select mode browses for a while
  altHeld: boolean
  previewLocale: string
  page: PreviewPage
  // requests to the preview: show these parts, open this page (the counters tell them apart)
  reveal: { keys: string[], count: number }
  navigation: { page: PreviewPage, count: number }
  // there are changes, which aren't saved
  dirty: boolean
  // the open panel's changes, shown in the preview
  previewPatch: PreviewPatch
  // brand colors shown in the preview, the saved ones when there are none
  previewBrand: PreviewBrand | null
  toasts: Toast[]
  confirmation: Confirmation | null
  // read out by screen readers (e.g. where an item was moved)
  announcement: string
}

export const useEditorStore = defineStore('editor', {
  state: (): EditorState => ({
    locale: 'en',
    restaurant: null,
    restaurants: [],
    user: null,
    urls: null,
    selection: null,
    previewHover: null,
    treeHover: null,
    mode: 'select',
    altHeld: false,
    previewLocale: 'en',
    page: {page: 'restaurant', menuId: null},
    reveal: {keys: [], count: 0},
    navigation: {page: {page: 'restaurant', menuId: null}, count: 0},
    dirty: false,
    previewPatch: {},
    previewBrand: null,
    toasts: [],
    confirmation: null,
    announcement: '',
  }),
  getters: {
    defaultLocale: (state): string => state.restaurant?.default_locale ?? 'en',
    locales: (state): string[] => state.restaurant?.supported_locales ?? ['en'],
    // the ones in the lists (archived ones are kept aside)
    menus: (state): EditorMenu[] => (state.restaurant?.menus ?? []).filter((m) => !m.archived),
    brand: (state): BrandColors => brandOf(state.restaurant ?? {brand_primary: null, brand_primary_content: null}),
    // the preview isn't selecting now (Browse mode, or Alt is held)
    browsing: (state): boolean => state.mode === 'browse' || state.altHeld,
  },
  actions: {
    hydrate(props: Partial<EditorState>) {
      Object.assign(this, props)

      this.previewLocale = this.restaurant?.default_locale ?? this.locale
    },

    /**
     * Open the panel of the part (none: the page structure). The preview shows it, when
     * it's picked outside of the preview. Unsaved changes of the open panel are saved or
     * discarded first, or it stays open.
     *
     * @param selection
     * @param reveal The preview scrolls to the part
     * @param force Leave the open panel without asking (e.g. after it was archived)
     */
    async select(selection: Selection | null, reveal: boolean = false, force: boolean = false): Promise<boolean> {
      if (!isSameSelection(selection, this.selection)) {
        if (!force && !await this.leave()) {
          return false
        }

        this.selection = selection
      }

      if (reveal && selection) {
        this.reveal = {keys: keysOf(selection), count: this.reveal.count + 1}
      }

      return true
    },

    /** The open panel registers what it does with its changes, when it's left. */
    guard(guard: PanelGuard) {
      panelGuard = guard
    },

    unguard(guard: PanelGuard) {
      if (panelGuard === guard) {
        panelGuard = null
      }
    },

    /**
     * Whether the open panel can be left: its changes are saved or discarded first, or it
     * stays open. Changes, which can't be saved (they're invalid), can only be discarded.
     */
    async leave(): Promise<boolean> {
      const guard = panelGuard

      if (!this.dirty || !guard) {
        return true
      }

      const canSave = guard.canSave()
      const answer = await this.ask({
        title: t('editor.leave.title'),
        message: t(canSave ? 'editor.leave.message' : 'editor.leave.message_invalid'),
        confirm: t(canSave ? 'editor.leave.save' : 'editor.leave.discard'),
        alternative: canSave ? t('editor.leave.discard') : undefined,
        danger: !canSave,
      })

      if (answer === 'cancel') {
        return false
      }

      if (answer === 'confirm' && canSave) {
        return guard.save()
      }

      guard.discard()

      return true
    },

    /** A part of the page was clicked in the preview. */
    selectKey(key: string) {
      const selection = selectionOf(key)

      if (selection) {
        this.select(selection)
      }
    },

    close() {
      return this.select(null)
    },

    /** Everything of the restaurant again, after its menus, categories or dishes changed. */
    async reload() {
      this.restaurant = (await getEditorRestaurant(this.restaurant!.id)).data.data
    },

    notify(message: string, action?: Toast['action']) {
      const id = ++toasts

      this.toasts = [...this.toasts, {id, message, action}]
      setTimeout(() => this.dismiss(id), TOAST_MS)
    },

    /** Read the message out to screen reader users. */
    announce(message: string) {
      // the same message again is read out too
      this.announcement = ''
      setTimeout(() => this.announcement = message, 50)
    },

    dismiss(id: number) {
      this.toasts = this.toasts.filter((toast) => toast.id !== id)
    },

    /** Ask a question: its answer. */
    ask(question: Omit<Confirmation, 'resolve'>): Promise<Answer> {
      this.confirmation?.resolve('cancel')

      return new Promise((resolve) => {
        this.confirmation = {...question, resolve}
      })
    },

    /** Ask a question: whether it's confirmed. */
    async confirm(question: Omit<Confirmation, 'resolve' | 'alternative'>): Promise<boolean> {
      return await this.ask(question) === 'confirm'
    },

    answer(answer: Answer) {
      this.confirmation?.resolve(answer)
      this.confirmation = null
    },

    /** Open a page in the preview. */
    openPage(page: PreviewPage) {
      this.page = page
      this.navigation = {page, count: this.navigation.count + 1}
    },

    findMenu(id: number | null): EditorMenu | null {
      return (this.restaurant?.menus ?? []).find((m) => m.id === id) ?? null
    },

    findCategory(id: number | null): EditorCategory | null {
      for (const menu of this.restaurant?.menus ?? []) {
        const category = (menu.categories ?? []).find((c) => c.id === id)

        if (category) {
          return category
        }
      }

      return null
    },

    /** The menu of the category. */
    menuOf(categoryId: number | null): EditorMenu | null {
      return (this.restaurant?.menus ?? [])
        .find((menu) => (menu.categories ?? []).some((category) => category.id === categoryId)) ?? null
    },

    findDish(id: number | null): EditorDish | null {
      for (const menu of this.restaurant?.menus ?? []) {
        for (const category of menu.categories ?? []) {
          const dish = (category.dishes ?? []).find((d) => d.id === id)

          if (dish) {
            return dish
          }
        }
      }

      return null
    },

    /**
     * Whether guests see the menu, category or dish: it and the ones it's in are neither
     * hidden nor archived.
     */
    isShown(selection: Selection): boolean {
      const shown = (item: { is_hidden: boolean, archived: boolean } | null) =>
        !!item && !item.is_hidden && !item.archived

      switch (selection.section) {
        case 'menu':
          return shown(this.findMenu(selection.id))
        case 'category': {
          const category = this.findCategory(selection.id)

          return shown(category) && shown(this.findMenu(category?.menu_id ?? null))
        }
        case 'dish': {
          const dish = this.findDish(selection.id)
          const category = this.findCategory(dish?.category_id ?? null)

          return shown(dish) && shown(category) && shown(this.findMenu(category?.menu_id ?? null))
        }
        default:
          return true
      }
    },
  },
})
