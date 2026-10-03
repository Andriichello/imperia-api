import {defineStore} from 'pinia'
import axios from 'axios'
import type {EditorRestaurant, EditorRestaurantItem, EditorVersion, Media} from '@/api'
import {
  activateEditorVersion,
  applyEditorVersion,
  deactivateEditorVersion,
  deleteEditorVersion,
  duplicateEditorVersion,
  putEditorVersionChange,
  removeEditorVersionChange,
  scheduleEditorVersion,
  updateEditorVersion,
} from '@/api'
import type {AdminUrls, AdminUser} from '@/admin/types'
import {translated} from '@/editor/translations'
import {nodesOf, Target, targetKey, TreeNode, treeOf} from '@/version/model'
import {t} from '@/i18n/utils'

/** How long typing pauses before it's saved into the version. */
const SAVE_MS = 500

// the timers of fields being typed in, by their keys
const timers: Record<string, ReturnType<typeof setTimeout>> = {}

// requests to the version go one after another: each responds with the whole version
let queue: Promise<unknown> = Promise.resolve()

export type VersionAction = 'schedule' | 'deactivate' | 'activate' | 'apply' | 'duplicate' | 'delete'

interface VersionState {
  // language of the admin itself
  locale: string
  user: AdminUser | null
  restaurants: EditorRestaurantItem[]
  urls: AdminUrls | null
  restaurant: EditorRestaurant | null
  version: EditorVersion | null
  // the item shown (a key of the tree)
  selected: string | null
  // language of the texts shown and edited
  contentLocale: string
  search: string
  changedOnly: boolean
  // open items of the tree
  expanded: string[]
  // requests on their way
  saving: number
  // why a field wasn't saved, by its key (`dish:12.title`)
  errors: Record<string, string>
  // values typed, which aren't in the version yet, by their keys: they're shown meanwhile
  typed: Record<string, unknown>
  // photos by their ids: the restaurant's, the dishes', the version's and uploaded ones
  photos: Record<number, Media>
  previewOpen: boolean
  // a message at the bottom, for a while
  toast: string | null
}

export const useVersionStore = defineStore('version', {
  state: (): VersionState => ({
    locale: 'en',
    user: null,
    restaurants: [],
    urls: null,
    restaurant: null,
    version: null,
    selected: null,
    contentLocale: 'en',
    search: '',
    changedOnly: false,
    expanded: [],
    saving: 0,
    errors: {},
    typed: {},
    photos: {},
    previewOpen: false,
    toast: null,
  }),
  getters: {
    defaultLocale: (state): string => state.restaurant?.default_locale ?? 'en',
    locales: (state): string[] => state.restaurant?.supported_locales ?? ['en'],
    // a version, which went live, isn't changed anymore
    readOnly: (state): boolean => state.version?.status === 'applied',

    tree(): TreeNode[] {
      return this.restaurant && this.version
        ? treeOf(this.restaurant, this.version, translated(this.restaurant.name, this.defaultLocale))
        : []
    },

    nodes(): Map<string, TreeNode> {
      return nodesOf(this.tree)
    },

    /** The item shown: the selected one, or the first one. */
    current(): TreeNode | null {
      return (this.selected ? this.nodes.get(this.selected) : null) ?? this.tree[1] ?? this.tree[0] ?? null
    },
  },
  actions: {
    hydrate(props: Partial<VersionState>) {
      Object.assign(this, props)

      this.contentLocale = this.defaultLocale

      for (const photo of [
        ...(this.restaurant?.photos ?? []),
        ...(this.restaurant?.menus ?? []).flatMap((menu) => (menu.categories ?? [])
          .flatMap((category) => (category.dishes ?? []).flatMap((dish) => dish.photos ?? []))),
        ...(this.version?.media ?? []),
      ]) {
        this.photos[photo.id] = photo
      }

      // the item of the URL, or the first one changed (the deepest)
      const wanted = new URLSearchParams(window.location.search).get('item')
      const changed = [...this.nodes.values()].filter((node) => node.change && node.kind !== 'size')

      this.selected = (wanted && this.nodes.has(wanted) ? wanted : null)
        ?? changed.find((node) => node.kind === 'dish')?.key
        ?? changed[0]?.key
        ?? null

      // items with changes are open (the first menu of a version without any), and the ones
      // the shown item is in
      this.expanded = [...this.nodes.values()]
        .filter((node) => node.children.length && node.count > 0)
        .map((node) => node.key)

      if (!this.expanded.length && this.tree[1]) {
        this.expanded = [this.tree[1].key, ...this.tree[1].children.slice(0, 1).map((node) => node.key)]
      }
      this.reveal(this.selected)
    },

    /** Open the item (a size opens its dish); the URL has it, so a reload shows it again. */
    select(key: string) {
      const node = this.nodes.get(key)

      this.selected = node?.opens ?? key
      this.reveal(this.selected)

      const url = new URL(window.location.href)
      url.searchParams.set('item', this.selected)
      window.history.replaceState(window.history.state, '', url.href)
    },

    /** Open the items, which the item is in. */
    reveal(key: string | null) {
      const parents = (nodes: TreeNode[], path: string[]): string[] | null => {
        for (const node of nodes) {
          if (node.key === key) {
            return path
          }

          const found = parents(node.children, [...path, node.key])

          if (found) {
            return found
          }
        }

        return null
      }

      const keys = parents(this.tree, []) ?? []
      this.expanded = [...new Set([...this.expanded, ...keys])]
    },

    toggle(key: string) {
      this.expanded = this.expanded.includes(key)
        ? this.expanded.filter((item) => item !== key)
        : [...this.expanded, key]
    },

    /** The value of the field shown: the one typed, till the version has it. */
    shown<T>(target: Target, field: string, value: T): T {
      const key = `${targetKey(target)}.${field}`

      return key in this.typed ? this.typed[key] as T : value
    },

    error(target: Target, field: string): string | null {
      return this.errors[`${targetKey(target)}.${field}`] ?? null
    },

    /**
     * Change the field from the version's date: it's shown right away and saved into the version,
     * once typing pauses (a value equal to the live one isn't a change anymore).
     */
    edit(target: Target, field: string, value: unknown, delay: number = SAVE_MS) {
      const key = `${targetKey(target)}.${field}`

      this.typed[key] = value
      clearTimeout(timers[key])
      timers[key] = setTimeout(() => this.send(target, {fields: {[field]: value}}, key, value), delay)
    },

    /** Change several fields together, right away (e.g. a menu's status: hidden and archived). */
    editFields(target: Target, values: Record<string, unknown>) {
      const prefix = targetKey(target)

      for (const [field, value] of Object.entries(values)) {
        clearTimeout(timers[`${prefix}.${field}`])
        this.typed[`${prefix}.${field}`] = value
      }

      this.send(target, {fields: values}).then(() => {
        for (const [field, value] of Object.entries(values)) {
          if (this.typed[`${prefix}.${field}`] === value) {
            delete this.typed[`${prefix}.${field}`]
          }
        }
      })
    },

    /** The fields aren't changed by the version anymore: they stay live. */
    revert(target: Target, fields: string[]) {
      for (const field of fields) {
        const key = `${targetKey(target)}.${field}`

        clearTimeout(timers[key])
        delete this.typed[key]
        delete this.errors[key]
      }

      this.send(target, {revert: fields})
    },

    /** Add a new item (e.g. a size of a dish) with its values. */
    add(target: Target, fields: Record<string, unknown>) {
      this.send(target, {fields})
    },

    /** One request to the version after the others; it responds with the whole version. */
    request(call: () => Promise<{ data: { data: EditorVersion } }>, key: string | null = null): Promise<boolean> {
      const run = queue.then(async () => {
        this.saving++

        try {
          this.version = (await call()).data.data

          for (const photo of this.version.media ?? []) {
            this.photos[photo.id] = photo
          }

          if (key) {
            delete this.errors[key]
          }

          return true
        } catch (e) {
          const errors = axios.isAxiosError(e) ? e.response?.data?.errors : null
          const message = errors ? Object.values(errors as Record<string, string[]>).flat()[0] : t('admin.version.failed')

          if (key) {
            this.errors[key] = message
          } else {
            this.notify(message)
          }

          return false
        } finally {
          this.saving--
        }
      })

      queue = run

      return run
    },

    async send(target: Target, body: { fields?: Record<string, unknown>, revert?: string[] }, key: string | null = null, value?: unknown) {
      const version = this.version!

      await this.request(() => putEditorVersionChange(version.id, {
        id: target.changeId ?? null,
        target_type: target.type,
        target_id: target.id,
        parent_id: target.parentId ?? null,
        ...body,
      } as never), key)

      // what's typed meanwhile stays
      if (key && this.typed[key] === value) {
        delete this.typed[key]
      }
    },

    /** Remove the item's change from the version (a new item isn't added). */
    remove(changeId: number) {
      return this.request(() => removeEditorVersionChange(this.version!.id, changeId) as never)
    },

    rename(name: string) {
      return this.request(() => updateEditorVersion(this.version!.id, {name: name.trim() || null}))
    },

    /** Go live at the date and time ("2026-11-01 00:00", the restaurant's time). */
    reschedule(goesLiveAt: string) {
      return this.request(() => updateEditorVersion(this.version!.id, {goes_live_at: goesLiveAt}))
    },

    async act(action: VersionAction): Promise<void> {
      const id = this.version!.id

      switch (action) {
        case 'schedule':
          await this.request(() => scheduleEditorVersion(id))
          break
        case 'deactivate':
          await this.request(() => deactivateEditorVersion(id))
          break
        case 'activate':
          await this.request(() => activateEditorVersion(id))
          break
        case 'apply':
          try {
            await applyEditorVersion(id)
            // the menus changed: everything again
            window.location.reload()
          } catch (e) {
            const version = axios.isAxiosError(e) ? e.response?.data?.data as EditorVersion | undefined : undefined

            if (version) {
              this.version = version
            }

            this.notify(version?.failure_reason ? t('admin.version.apply_failed', {reason: version.failure_reason}) : t('admin.version.failed'))
          }
          break
        case 'duplicate': {
          const copy = (await duplicateEditorVersion(id)).data.data
          window.location.assign(`${this.urls!.version}/${copy.id}`)
          break
        }
        case 'delete':
          await deleteEditorVersion(id)
          window.location.assign(this.urls!.dashboard)
          break
      }
    },

    notify(message: string) {
      this.toast = message
      setTimeout(() => {
        if (this.toast === message) {
          this.toast = null
        }
      }, 6000)
    },
  },
})
