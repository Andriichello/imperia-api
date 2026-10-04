import {defineStore} from 'pinia'
import {watch} from 'vue'
import axios from 'axios'
import {DateTime} from 'luxon'
import type {EditorCategory, EditorDish, EditorMenu, EditorRestaurant, EditorRestaurantItem, EditorVersion} from '@/api'
import {getEditorRestaurant, putEditorVersionChange, storeEditorVersion} from '@/api'
import type {PreviewBrand, PreviewMode, PreviewPage, PreviewPatch} from '@/editor/protocol'
import {
  draftKey,
  isNewId,
  isSameSelection,
  keysOf,
  pageParam,
  parsePageParam,
  parseSelectionParam,
  Section,
  Selection,
  selectionOf,
  selectionParam,
} from '@/editor/sections'
import {brandOf, BrandColors, isHex} from '@/editor/brand'
import {
  bySaveOrder,
  canonical,
  copy,
  DraftEntry,
  draftsPreview,
  isChanged,
  keyedErrors,
  KINDS,
  savedOf,
} from '@/editor/items'
import {findCategory, findDish, findMenu} from '@/editor/find'
import {schedulingOf, Scheduling} from '@/editor/schedule'
import {t} from '@/i18n/utils'
import {formatDateTime} from '@/admin/format'

export interface EditorUser {
  id: number
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
  // of a version: this one and `/{id}`
  version: string
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

/** How long a toast stays. */
const TOAST_MS = 6000

/** How long drafts wait for typing to stop before they're kept in the browser. */
const PERSIST_MS = 300

/** The drafts of the store (its state's type loses theirs). */
const entriesOf = (drafts: unknown): DraftEntry[] => Object.values(drafts as Record<string, DraftEntry>)

let toasts = 0
let persistTimer: ReturnType<typeof setTimeout> | null = null

/** Drafts kept in the browser (see `persist()`). */
interface StoredDrafts {
  lastNewId: number
  drafts: Pick<DraftEntry, 'key' | 'selection' | 'values'>[]
}

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
  // the open panel shows the part of the dish picked in the preview again (it counts the picks)
  fieldPicks: number
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
  // changes, which aren't saved yet, by the part's key ("notes", "dish:12"): switching parts
  // keeps them, they're kept in the browser over reloads too
  drafts: Record<string, DraftEntry>
  // sections, which have or had drafts: the preview shows their saved values again after them
  touched: Section[]
  // the last id of a new menu, category or dish (they count down from -1)
  lastNewId: number
  saving: boolean
  // photos being uploaded
  uploads: number
  // the list of unsaved items is open
  reviewOpen: boolean
  // scheduling the drafts for later
  scheduleOpen: boolean
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
    fieldPicks: 0,
    previewHover: null,
    treeHover: null,
    mode: 'select',
    altHeld: false,
    previewLocale: 'en',
    page: {page: 'restaurant', menuId: null},
    reveal: {keys: [], count: 0},
    navigation: {page: {page: 'restaurant', menuId: null}, count: 0},
    drafts: {},
    touched: [],
    lastNewId: 0,
    saving: false,
    uploads: 0,
    reviewOpen: false,
    scheduleOpen: false,
    toasts: [],
    confirmation: null,
    announcement: '',
  }),
  getters: {
    defaultLocale: (state): string => state.restaurant?.default_locale ?? 'en',
    locales: (state): string[] => state.restaurant?.supported_locales ?? ['en'],
    // the ones in the lists (archived ones are kept aside)
    menus: (state): EditorMenu[] => (state.restaurant?.menus ?? []).filter((m) => !m.archived),
    // versions, which haven't gone live yet (the panels show what's planned for their items)
    versions: (state): EditorVersion[] => state.restaurant?.versions ?? [],
    // the saved ones
    brand: (state): BrandColors => brandOf(state.restaurant ?? {brand_primary: null, brand_primary_content: null}),
    // the preview isn't selecting now (Browse mode, or Alt is held)
    browsing: (state): boolean => state.mode === 'browse' || state.altHeld,

    /** Drafts, which change something of a part, which is there. */
    unsaved(state): DraftEntry[] {
      const restaurant = state.restaurant

      return restaurant
        ? entriesOf(state.drafts).filter((entry) => KINDS[entry.selection.section].exists(restaurant, entry.selection)
          && isChanged(entry, restaurant))
        : []
    },

    /** The public page's data with the drafts, in the preview's language. */
    previewPatch(): PreviewPatch {
      return this.restaurant
        ? draftsPreview(this.restaurant, this.unsaved, new Set(this.touched), this.previewLocale)
        : {}
    },

    /** Brand colors of the draft (valid ones only), none for the saved ones. */
    previewBrand(): PreviewBrand | null {
      const draft = this.unsaved.find((entry) => entry.key === 'brand')?.values as BrandColors | undefined

      return draft && isHex(draft.primary) && isHex(draft.content) && isHex(draft.accent)
        ? {primary: draft.primary, content: draft.content, accent: draft.accent}
        : null
    },

    /**
     * Drafts, which can be scheduled for later (they're valid, a version can take something of
     * them), with their changes.
     */
    schedulable(): { entry: DraftEntry, scheduling: Scheduling }[] {
      const restaurant = this.restaurant

      return restaurant
        ? bySaveOrder(this.unsaved)
          .filter((entry) => KINDS[entry.selection.section].valid(entry.values, restaurant, entry.selection))
          .map((entry) => ({entry, scheduling: schedulingOf(entry, restaurant)}))
          .filter(({scheduling}) => scheduling.changes.length > 0)
        : []
    },

    /** Keys of the parts of the public page, which have drafts. */
    unsavedKeys(): string[] {
      return this.unsaved.flatMap((entry) => keysOf(entry.selection))
    },
  },
  actions: {
    hydrate(props: Partial<EditorState>) {
      Object.assign(this, props)

      this.previewLocale = this.restaurant?.default_locale ?? this.locale
      this.restoreDrafts()
      this.followUrl(true)

      // the drafts are kept in the browser, once typing stops
      watch(() => this.drafts, () => {
        if (persistTimer) {
          clearTimeout(persistTimer)
        }

        persistTimer = setTimeout(() => this.persist(), PERSIST_MS)
      }, {deep: true})

      // the preview shows the saved values of the sections again, once their drafts are gone
      watch(() => this.unsaved.map((entry) => entry.selection.section), (sections) => {
        const added = sections.filter((section) => !this.touched.includes(section))

        if (added.length) {
          this.touched = [...new Set([...this.touched, ...added])]
        }
      }, {immediate: true})

      // the URL has the page of the preview, so a reload shows it again
      watch(() => this.page, () => this.writeUrl('replace'), {deep: true})

      // a part of a dish is picked on the dish's page only
      watch(() => this.page.page, (page) => {
        if (page !== 'dish' && this.selection?.field) {
          this.selection = {...this.selection, field: null}
        }
      })
    },

    // Drafts

    /**
     * The draft of the part, which its panel edits: the saved values, till they're changed.
     */
    draftOf<T>(selection: Selection): DraftEntry<T> {
      const key = draftKey(selection)

      if (!this.drafts[key]) {
        this.drafts[key] = {
          key,
          selection: {section: selection.section, id: selection.id, parent: selection.parent ?? null},
          values: copy(savedOf(this.restaurant!, selection)),
          errors: {},
          failed: false,
        }
      }

      return this.drafts[key] as DraftEntry<T>
    },

    isUnsaved(selection: Selection): boolean {
      const key = draftKey(selection)

      return this.unsaved.some((entry) => entry.key === key)
    },

    /** Drafts of new menus, categories or dishes added to the one (new menus: to the restaurant). */
    newItems(section: 'menu' | 'category' | 'dish', parent: number | null = null): DraftEntry[] {
      return this.unsaved.filter((entry) => entry.selection.section === section && isNewId(entry.selection.id)
        && (section === 'menu' || entry.selection.parent === parent))
    },

    /** Whether any of the sections has a draft (e.g. any menu, category or dish). */
    hasUnsaved(sections: Section[]): boolean {
      return this.unsaved.some((entry) => sections.includes(entry.selection.section))
    },

    /**
     * Drafts, which don't change anything (or whose parts are gone), are left out, but
     * the open panel's one.
     */
    prune() {
      const restaurant = this.restaurant
      const open = this.selection ? draftKey(this.selection) : null

      if (!restaurant) {
        return
      }

      for (const entry of entriesOf(this.drafts)) {
        const key = entry.key

        if (key !== open && (!KINDS[entry.selection.section].exists(restaurant, entry.selection) || !isChanged(entry, restaurant))) {
          delete this.drafts[key]
        }
      }
    },

    /**
     * Discard the draft: its part has the saved values again. A new one is left out, and its
     * panel goes back to the one it was added in.
     */
    discard(key: string) {
      const entry = this.drafts[key]

      if (!entry) {
        return
      }

      if (isNewId(entry.selection.id)) {
        delete this.drafts[key]

        if (this.selection && draftKey(this.selection) === key) {
          this.select(this.parentOf(entry.selection), false, 'replace')
        }

        return
      }

      entry.values = copy(savedOf(this.restaurant!, entry.selection))
      entry.errors = {}
      entry.failed = false
      this.prune()
    },

    /** Discard the draft of an item of the list of unsaved ones, which can be undone. */
    revert(key: string, name: string) {
      const entry = this.drafts[key]

      if (!entry) {
        return
      }

      const selection = {...entry.selection}
      const values = copy(entry.values)

      this.discard(key)
      this.notify(t('editor.unsaved.reverted', {name}), {
        label: t('editor.actions.undo'),
        run: () => {
          this.draftOf(selection).values = values
        },
      })
    },

    /** Discard every draft, once it's confirmed. */
    async discardAll(): Promise<boolean> {
      const count = this.unsaved.length

      if (!count) {
        return true
      }

      const confirmed = await this.confirm({
        title: t('editor.save.discard_title', {count}, count),
        message: t('editor.save.discard_message'),
        confirm: t('editor.save.discard_confirm'),
        danger: true,
      })

      if (!confirmed) {
        return false
      }

      for (const key of Object.keys(this.drafts)) {
        this.discard(key)
      }

      this.reviewOpen = false

      return true
    },

    /**
     * Save every draft, which can be saved (the ones with mistakes are left for later): one by
     * one, then the restaurant is loaded again. The ones, which weren't saved, keep their
     * errors. New menus, categories and dishes are open with their ids afterwards.
     */
    async saveAll(): Promise<boolean> {
      if (this.saving || !this.restaurant || this.uploads) {
        return false
      }

      const start = this.restaurant
      const entries = bySaveOrder(this.unsaved)
      const ready = entries.filter((entry) => KINDS[entry.selection.section].valid(entry.values, start, entry.selection))
      const invalid = entries.filter((entry) => !ready.includes(entry))

      // values, which were saved, by their drafts' keys
      const sent = new Map<string, string>()
      const created = new Map<string, number>()
      let restaurant = start
      let fresh = true
      let failed = 0

      this.saving = true

      for (const entry of ready) {
        const values = copy(entry.values)

        try {
          const result = await KINDS[entry.selection.section].save(values, restaurant, entry.selection)

          fresh = !!result.restaurant
          restaurant = result.restaurant ?? restaurant
          entry.errors = {}
          entry.failed = false
          sent.set(entry.key, canonical(values))

          if (result.id) {
            created.set(entry.key, result.id)
          }
        } catch (e) {
          if (axios.isAxiosError(e) && e.response?.status === 422) {
            entry.errors = keyedErrors(e.response.data?.errors ?? {}, entry, values)
          } else {
            entry.failed = true
          }

          failed++
        }
      }

      try {
        this.setRestaurant(fresh ? restaurant : (await getEditorRestaurant(start.id)).data.data, sent)
      } catch (e) {
        // the saved drafts are compared with the restaurant, once it's loaded again
        failed++
      }

      // new items are open with their ids
      for (const [key, id] of created) {
        const entry = this.drafts[key]

        delete this.drafts[key]

        if (entry && this.selection && draftKey(this.selection) === key) {
          this.select({section: entry.selection.section, id}, true, 'replace')
        }
      }

      this.saving = false
      this.prune()

      const left = failed + invalid.length

      if (left) {
        const first = [...entries].find((entry) => this.drafts[entry.key] && (entry.failed
          || Object.keys(entry.errors).length || invalid.includes(entry)))

        this.notify(t('editor.save.not_saved', {count: left}, left), first
          ? {label: t('editor.save.show'), run: () => this.select(first.selection, true)}
          : undefined)
      } else {
        this.reviewOpen = false
        this.announce(t('editor.save.saved'))
      }

      return !left
    },

    /**
     * The restaurant after it changed: the drafts, which were saved as they are, and the ones,
     * which changed nothing, get its values. The others are kept in line with it (e.g. without
     * the categories, which were archived meanwhile).
     *
     * @param restaurant
     * @param sent Values of the drafts, which were saved, by their keys
     */
    setRestaurant(restaurant: EditorRestaurant, sent: Map<string, string> = new Map()) {
      const before = this.restaurant

      this.restaurant = restaurant

      for (const entry of entriesOf(this.drafts)) {
        const kind = KINDS[entry.selection.section]

        if (isNewId(entry.selection.id) || !kind.exists(restaurant, entry.selection)) {
          continue
        }

        const values = canonical(entry.values)
        const wasClean = !!before && kind.exists(before, entry.selection)
          && values === canonical(kind.saved(before, entry.selection))

        if (wasClean || sent.get(entry.key) === values) {
          entry.values = copy(kind.saved(restaurant, entry.selection))
        } else if (kind.reconcile) {
          entry.values = kind.reconcile(entry.values, restaurant, entry.selection)
        }
      }

      this.prune()
    },

    /**
     * Schedule the drafts for later: in a new version of their own, which goes live at the date
     * (in the restaurant's time zone), or in a version, which is planned already. What's
     * scheduled leaves the drafts; what a version can't change stays.
     *
     * @return An error, none when they're scheduled
     */
    async scheduleDrafts(target: { goesLiveAt: string } | { versionId: number }): Promise<string | null> {
      const restaurant = this.restaurant
      const plans = this.schedulable

      if (!restaurant || !plans.length || this.saving) {
        return t('editor.schedule.nothing')
      }

      const changes = plans.flatMap(({scheduling}) => scheduling.changes)
      let version: EditorVersion | null = null

      this.saving = true

      try {
        if ('goesLiveAt' in target) {
          version = (await storeEditorVersion(restaurant.id, {goes_live_at: target.goesLiveAt, schedule: true, changes})).data.data
        } else {
          for (const change of changes) {
            version = (await putEditorVersionChange(target.versionId, change)).data.data
          }
        }
      } catch (e) {
        const errors = axios.isAxiosError(e) && e.response?.status === 422 ? e.response.data?.errors : null
        const message = errors ? Object.values(errors as Record<string, string[]>).flat()[0] : null

        return message ?? t('editor.schedule.failed')
      } finally {
        this.saving = false
      }

      for (const {entry, scheduling} of plans) {
        if (scheduling.rest !== null) {
          entry.values = scheduling.rest
        } else if (isNewId(entry.selection.id)) {
          // a new dish is created, when the version goes live
          this.discard(entry.key)
        } else {
          entry.values = copy(savedOf(restaurant, entry.selection))
        }
      }

      this.prune()
      this.scheduleOpen = false
      this.reviewOpen = false

      try {
        await this.reload()
      } catch (e) {
        // the versions are shown, once the restaurant is loaded again
      }

      if (version) {
        this.notify(t('editor.schedule.scheduled', {date: version.goes_live_at ? formatDateTime(
          DateTime.fromISO(version.goes_live_at, {setZone: true}), this.locale,
        ) : ''}), this.urls ? {label: t('editor.schedule.manage'), run: () => window.location.assign(this.versionUrl(version!.id))} : undefined)
      }

      return null
    },

    /** Page of the version. */
    versionUrl(id: number): string {
      return `${this.urls?.version ?? ''}/${id}`
    },

    /** Everything of the restaurant again, after its menus, categories or dishes changed. */
    async reload() {
      this.setRestaurant((await getEditorRestaurant(this.restaurant!.id)).data.data)
    },

    /** Where the drafts are kept in this browser: per restaurant and user. */
    storageKey(): string {
      return `editor-drafts:${this.restaurant?.id}:${this.user?.id ?? 0}`
    },

    /** Keep the drafts in the browser (none, when there are no changes). */
    persist() {
      if (persistTimer) {
        clearTimeout(persistTimer)
        persistTimer = null
      }

      const stored: StoredDrafts = {
        lastNewId: this.lastNewId,
        drafts: this.unsaved.map(({key, selection, values}) => ({key, selection, values})),
      }

      try {
        if (stored.drafts.length) {
          localStorage.setItem(this.storageKey(), JSON.stringify(stored))
        } else {
          localStorage.removeItem(this.storageKey())
        }
      } catch (e) {
        // no storage (e.g. it's blocked or full): the drafts are kept till the page is closed
      }
    },

    /** The drafts kept in the browser, of the parts, which are still there. */
    restoreDrafts() {
      let stored: StoredDrafts | null = null

      try {
        stored = JSON.parse(localStorage.getItem(this.storageKey()) ?? 'null')
      } catch (e) {
        // none, or not readable
      }

      const restaurant = this.restaurant

      if (!stored || !restaurant || !Array.isArray(stored.drafts)) {
        return
      }

      for (const item of stored.drafts) {
        const kind = KINDS[item?.selection?.section]

        if (kind && item.key === draftKey(item.selection) && kind.exists(restaurant, item.selection)) {
          this.drafts[item.key] = {...item, errors: {}, failed: false}
        }
      }

      this.lastNewId = Math.min(stored.lastNewId ?? 0, ...entriesOf(this.drafts).map((entry) => entry.selection.id ?? 0), 0)
      this.prune()
    },

    // Selection

    /**
     * Open the panel of the part (none: the page structure). The preview shows it, when
     * it's picked outside of the preview. Drafts of the open panel are kept.
     *
     * @param selection
     * @param reveal The preview scrolls to the part
     * @param history A new entry of the browser's history (Back opens the panel before), or
     *                the same one (e.g. a new item was saved), or none (Back and Forward)
     */
    select(selection: Selection | null, reveal: boolean = false, history: 'push' | 'replace' | 'none' = 'push') {
      const same = isSameSelection(selection, this.selection)

      this.selection = selection

      if (selection?.field) {
        this.fieldPicks++
      }

      if (!same) {
        this.prune()

        if (history !== 'none') {
          this.writeUrl(history)
        }
      }

      if (reveal && selection) {
        this.reveal = {keys: keysOf(selection), count: this.reveal.count + 1}
      }
    },

    /** Add a new menu, category or dish to the one it's in (a new menu to the restaurant). */
    add(section: 'menu' | 'category' | 'dish', parent: number | null = null) {
      this.lastNewId = Math.min(this.lastNewId, 0) - 1
      this.select({section, id: this.lastNewId, parent})
    },

    /** The panel, which a new item was added in. */
    parentOf(selection: Selection): Selection | null {
      switch (selection.section) {
        case 'menu':
          return {section: 'menus', id: null}
        case 'category':
          return selection.parent ? {section: 'menu', id: selection.parent} : null
        case 'dish':
          return selection.parent ? {section: 'category', id: selection.parent} : null
        default:
          return null
      }
    },

    /**
     * A part of the page was clicked in the preview: on a dish page, a part of the dish
     * (it's open in its panel already, or it opens).
     */
    selectKey(key: string) {
      const dishId = this.page.page === 'dish' ? (this.page.dishId ?? null) : null
      const selection = selectionOf(key, dishId)

      if (selection) {
        this.select(selection)
      }
    },

    close() {
      this.select(null)
    },

    /** The selection and the page of the URL (after Back or Forward, or when it's opened). */
    followUrl(initial: boolean = false) {
      const params = new URLSearchParams(window.location.search)
      let selection = parseSelectionParam(params.get('select'))

      if (selection && isNewId(selection.id)) {
        // a new one is there only as a draft
        const entry = this.drafts[draftKey(selection)]
        selection = entry ? {...entry.selection} : null
      } else if (selection && !KINDS[selection.section].exists(this.restaurant!, selection)) {
        selection = null
      }

      const page = this.pageOf(params.get('page'))

      if (initial) {
        this.page = page
        this.navigation = {page, count: 0}
        this.selection = selection

        // a link to a part of the page shows it in the preview
        if (selection && !params.get('page')) {
          this.reveal = {keys: keysOf(selection), count: this.reveal.count + 1}
        }

        return
      }

      this.select(selection, false, 'none')

      if (pageParam(page) !== pageParam(this.page)) {
        this.openPage(page)
      }
    },

    /** The page of the URL's parameter: a menu or a dish guests can open, or the restaurant page. */
    pageOf(value: string | null): PreviewPage {
      const param = parsePageParam(value)

      if (param?.page === 'menu' && this.isShown({section: 'menu', id: param.id})) {
        return {page: 'menu', menuId: param.id}
      }

      if (param?.page === 'dish' && this.isShown({section: 'dish', id: param.id})) {
        return {page: 'dish', menuId: findDish(this.restaurant, param.id)!.menu_id, dishId: param.id}
      }

      return {page: 'restaurant', menuId: null}
    },

    writeUrl(history: 'push' | 'replace') {
      const url = new URL(window.location.href)
      const select = selectionParam(this.selection)
      const page = pageParam(this.page)

      select ? url.searchParams.set('select', select) : url.searchParams.delete('select')
      page ? url.searchParams.set('page', page) : url.searchParams.delete('page')

      if (url.href !== window.location.href) {
        window.history[history === 'push' ? 'pushState' : 'replaceState'](window.history.state, '', url.href)
      }
    },

    // Messages

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

    // Preview

    /** Open a page in the preview. */
    openPage(page: PreviewPage) {
      this.page = page
      this.navigation = {page, count: this.navigation.count + 1}
    },

    // Items

    findMenu(id: number | null): EditorMenu | null {
      return findMenu(this.restaurant, id)
    },

    findCategory(id: number | null): EditorCategory | null {
      return findCategory(this.restaurant, id)
    },

    /** The menu of the category. */
    menuOf(categoryId: number | null): EditorMenu | null {
      return findMenu(this.restaurant, findCategory(this.restaurant, categoryId)?.menu_id)
    },

    findDish(id: number | null): EditorDish | null {
      return findDish(this.restaurant, id)
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
