import type {Pinia} from 'pinia'
import {watch} from 'vue'
import type {Dish, DishMenu} from '@/api'
import {useAppStore} from '@/stores/app'
import {usePreviewStore} from '@/stores/preview'
import {
  EDIT_KEY_ATTRIBUTE,
  EditorMessage,
  isBridgeMessage,
  keyId,
  keyKind,
  PreviewMessage,
  PreviewBrand,
  PreviewMode,
  PreviewPage,
  PreviewPatch,
} from '@/editor/protocol'

/**
 * The public page's side of the editor's preview: it's loaded only there (see `isEditorPreview`).
 *
 * In Select mode clicks pick the parts of the page marked with `editKey()` (and never open
 * anything), the hovered and selected parts are outlined. In Browse mode, or while Alt is held,
 * the page works like for guests. The outlines are one overlay on top of the page, so its
 * components keep their looks.
 */

/** Colors of the editor's selection: its own ones, never the restaurant's brand colors. */
const BLUE = '#2563eb'
const BLUE_TEXT = '#1d4ed8'

/** Parts of the restaurant page; the other ones are on menu pages. */
const RESTAURANT_KINDS = ['photos', 'details', 'notes', 'menus', 'hours', 'contact']

/** How long to wait for a part after opening its page (dishes are loaded after it opens). */
const WAIT_MS = 4000

/** Height of the outlines' label chips. */
const CHIP_HEIGHT = 20

interface Rect {
  top: number
  left: number
  right: number
  bottom: number
}

function selectorOf(key: string): string {
  return `[${EDIT_KEY_ATTRIBUTE}="${CSS.escape(key)}"]`
}

/**
 * The new items in their order: the current ones (by id) updated with their new values,
 * so whoever holds them keeps them.
 */
function mergeById<T extends { id: number }>(
  current: T[],
  next: T[],
  update: (item: T, values: T) => void = (item, values) => Object.assign(item, values),
): T[] {
  const byId = new Map(current.map((item) => [item.id, item]))

  return next.map((values) => {
    const item = byId.get(values.id)

    if (!item) {
      return values
    }

    update(item, values)

    return item
  })
}

function styled<K extends keyof HTMLElementTagNameMap>(tag: K, style: Partial<CSSStyleDeclaration>): HTMLElementTagNameMap[K] {
  const element = document.createElement(tag)
  Object.assign(element.style, style)

  return element
}

class PreviewBridge {
  protected mode: PreviewMode = 'select'
  protected altHeld = false
  protected labels: Record<string, string> = {}
  protected hoverTemplate = '{label}'
  protected selectedKeys: string[] = []
  protected selectedLabel: string | null = null
  // hovered with the pointer or focused with the keyboard
  protected hoverKey: string | null = null
  // hovered in the editor's page structure
  protected treeHoverKeys: string[] = []
  protected page: PreviewPage
  protected overlay: HTMLDivElement
  protected frame: number | null = null
  // the unsaved changes shown now: they're shown again on the dishes, once they're loaded
  protected draft: PreviewPatch = {}
  protected applyingDraft = false

  constructor(
    protected app: ReturnType<typeof useAppStore>,
    protected preview: ReturnType<typeof usePreviewStore>,
  ) {
    this.page = this.currentPage()

    // outside of the body, so redrawing it doesn't count as a change of the page
    this.overlay = styled('div', {
      position: 'fixed',
      inset: '0',
      pointerEvents: 'none',
      zIndex: '2147483647',
    })
  }

  install(): void {
    document.documentElement.appendChild(this.overlay)
    document.head.appendChild(this.styles())
    this.applyMode()

    window.addEventListener('message', (e) => this.onMessage(e))

    // capturing: before any listener of the page
    window.addEventListener('click', (e) => this.onClick(e), true)
    for (const type of ['pointerdown', 'mousedown', 'touchstart']) {
      window.addEventListener(type, (e) => this.onPress(e as MouseEvent | TouchEvent), true)
    }
    window.addEventListener('keydown', (e) => this.onKeyDown(e), true)
    window.addEventListener('keyup', (e) => e.key === 'Alt' && this.setAlt(false))
    // after the page, which may use it itself (e.g. to close a drawer)
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !e.defaultPrevented) {
        this.post({type: 'editor:escape'})
      }
    })
    window.addEventListener('blur', () => this.setAlt(false))

    window.addEventListener('pointermove', (e) => {
      this.setAlt(e.altKey)
      this.setHover(this.keyOf(e.target))
    }, {passive: true})
    document.documentElement.addEventListener('pointerleave', () => this.setHover(null))
    window.addEventListener('focusin', (e) => this.setHover(this.keyOf(e.target)))

    // the outlines follow the parts of the page
    window.addEventListener('scroll', () => this.render(), {passive: true, capture: true})
    window.addEventListener('resize', () => this.render())
    new ResizeObserver(() => this.render()).observe(document.body)
    new MutationObserver(() => this.render()).observe(document.body, {childList: true, subtree: true})

    this.watchNavigation()

    // the page loads its dishes after it opens
    watch(() => this.preview.products, () => {
      if (!this.applyingDraft && this.draft.products) {
        this.applyDraft(this.draft)
      }
    }, {flush: 'sync'})

    this.post({type: 'editor:ready', page: this.page, locale: this.app.locale})
  }

  protected post(message: PreviewMessage): void {
    window.parent.postMessage(message, window.location.origin)
  }

  protected onMessage(event: MessageEvent): void {
    if (event.origin !== window.location.origin || event.source !== window.parent) {
      return
    }

    const message = event.data

    if (!isBridgeMessage<EditorMessage>(message)) {
      return
    }

    switch (message.type) {
      case 'editor:labels':
        this.labels = message.labels
        this.hoverTemplate = message.hover
        break
      case 'editor:mode':
        this.mode = message.mode
        this.applyMode()
        break
      case 'editor:alt':
        this.setAlt(message.held, false)
        break
      case 'editor:select':
        this.selectedKeys = message.keys
        this.selectedLabel = message.label
        break
      case 'editor:hover':
        this.treeHoverKeys = message.keys
        break
      case 'editor:scrollTo':
        this.scrollTo(message.keys)
        break
      case 'editor:navigate':
        this.navigate(message.page)
        break
      case 'editor:locale':
        this.switchLocale(message.locale)
        return
      case 'editor:draft':
        this.applyDraft(message.patch)
        break
      case 'editor:brand':
        this.applyBrand(message.brand)
        break
    }

    this.render()
  }

  /**
   * Show the unsaved changes: they replace the page's data, which it shows reactively.
   * Menus, categories and dishes are updated in place (by their ids): the page keeps
   * the ones it shows (e.g. the selected menu).
   */
  protected applyDraft(patch: PreviewPatch): void {
    this.draft = {...this.draft, ...patch}
    this.applyingDraft = true

    if (patch.restaurant && this.app.restaurant) {
      Object.assign(this.app.restaurant, patch.restaurant)
    }

    if (patch.menus) {
      this.app.menus = mergeById(this.app.menus ?? [], patch.menus, (menu, next) => {
        const {categories, ...values} = next

        Object.assign(menu, values)
        menu.categories = mergeById(menu.categories ?? [], categories ?? [])
      })
    }

    // the dishes aren't loaded yet: they'll be shown once they are
    if (patch.products && this.preview.products) {
      this.preview.products = mergeById(this.preview.products, patch.products)
    }

    this.applyingDraft = false
  }

  /** Brand colors instead of the restaurant's ones (they're set on the body, see `web/app.blade.php`). */
  protected applyBrand(brand: PreviewBrand): void {
    document.body.style.setProperty('--color-warning', brand.primary)
    document.body.style.setProperty('--color-warning-content', brand.content)
  }

  /** Whether clicks select parts of the page now (Select mode, Alt isn't held). */
  protected selecting(): boolean {
    return this.mode === 'select' && !this.altHeld
  }

  protected applyMode(): void {
    document.documentElement.dataset.editorMode = this.selecting() ? 'select' : 'browse'
  }

  protected setAlt(held: boolean, report: boolean = true): void {
    if (held === this.altHeld) {
      return
    }

    this.altHeld = held
    this.applyMode()

    if (report) {
      this.post({type: 'editor:alt', held})
    }

    this.render()
  }

  protected setHover(key: string | null): void {
    if (key === this.hoverKey) {
      return
    }

    this.hoverKey = key
    this.post({type: 'editor:hover', key: this.selecting() ? key : null})
    this.render()
  }

  protected keyOf(target: EventTarget | null): string | null {
    if (!(target instanceof Element)) {
      return null
    }

    return target.closest(`[${EDIT_KEY_ATTRIBUTE}]`)?.getAttribute(EDIT_KEY_ATTRIBUTE) ?? null
  }

  /** Select mode: a click picks the part of the page, and does nothing else. */
  protected onClick(event: MouseEvent): void {
    this.setAlt(event.altKey)

    if (!this.selecting()) {
      return
    }

    event.preventDefault()
    event.stopImmediatePropagation()

    const key = this.keyOf(event.target)

    if (key) {
      this.post({type: 'editor:select', key})
    }
  }

  /** Select mode: nothing starts on a press either (e.g. dragging a photo carousel). */
  protected onPress(event: MouseEvent | TouchEvent): void {
    this.setAlt(event.altKey)

    if (this.selecting()) {
      event.stopPropagation()
    }
  }

  protected onKeyDown(event: KeyboardEvent): void {
    if (event.key === 'Alt') {
      this.setAlt(true)
      return
    }

    if (!this.selecting() || (event.key !== 'Enter' && event.key !== ' ')) {
      return
    }

    const key = this.keyOf(event.target)
    const control = event.target instanceof Element
      && event.target.closest('button, a, [role="button"]')

    if (!key && !control) {
      return
    }

    event.preventDefault()
    event.stopImmediatePropagation()

    if (key) {
      this.post({type: 'editor:select', key})
    }
  }

  protected styles(): HTMLStyleElement {
    const style = document.createElement('style')

    style.textContent = `
      html[data-editor-mode="select"] body { user-select: none; -webkit-user-select: none; }
      html[data-editor-mode="select"] [${EDIT_KEY_ATTRIBUTE}],
      html[data-editor-mode="select"] [${EDIT_KEY_ATTRIBUTE}] * { cursor: pointer; }
      html[data-editor-mode="select"] [${EDIT_KEY_ATTRIBUTE}]:focus { outline: none; }
    `

    return style
  }

  protected render(): void {
    if (this.frame !== null) {
      return
    }

    this.frame = requestAnimationFrame(() => {
      this.frame = null
      this.draw()
    })
  }

  protected draw(): void {
    const elements: HTMLElement[] = []

    // Browse mode works like the public page: no outlines
    if (this.selecting()) {
      this.selectedKeys.forEach((key, index) => {
        const label = index === 0 ? (this.selectedLabel ?? this.labelOf(key)) : null
        elements.push(...this.outline(key, true, label))
      })

      const hovered = [...new Set([this.hoverKey, ...this.treeHoverKeys])]
        .filter((key): key is string => !!key && !this.selectedKeys.includes(key))

      for (const key of hovered) {
        const label = key === this.hoverKey
          ? this.hoverTemplate.replace('{label}', this.labelOf(key))
          : this.labelOf(key)

        elements.push(...this.outline(key, false, label))
      }
    }

    this.overlay.replaceChildren(...elements)
  }

  protected labelOf(key: string): string {
    return this.labels[key] ?? this.labels[keyKind(key)] ?? keyKind(key)
  }

  /** Outline of the part (with its label), only the part of it, which isn't under the sticky menus. */
  protected outline(key: string, selected: boolean, label: string | null): HTMLElement[] {
    const rect = this.rectOf(key)

    if (!rect) {
      return []
    }

    const clip = key === 'menu-tabs' ? 0 : this.stickyBottom()
    const top = Math.max(rect.top, clip)
    // within the page's width (some parts are nudged a bit out of it)
    const left = Math.max(rect.left, 0)
    const right = Math.min(rect.right, document.documentElement.clientWidth)

    if (rect.bottom - top < 4 || right - left < 4) {
      return []
    }

    const box = styled('div', {
      position: 'fixed',
      left: `${left}px`,
      top: `${top}px`,
      width: `${right - left}px`,
      height: `${rect.bottom - top}px`,
      border: `2px ${selected ? 'solid' : 'dashed'} ${BLUE}`,
      borderRadius: '2px',
      boxSizing: 'border-box',
    })

    if (!label) {
      return [box]
    }

    // on the top edge, or inside when there's no room above it
    const chipTop = top - CHIP_HEIGHT >= clip ? top - CHIP_HEIGHT + 2 : top + 2
    const chip = styled('div', {
      position: 'fixed',
      left: `${left + 8}px`,
      top: `${chipTop}px`,
      maxWidth: `${Math.max(right - left - 16, 40)}px`,
      height: `${CHIP_HEIGHT}px`,
      padding: '0 6px',
      border: `1px solid ${BLUE}`,
      borderRadius: '4px',
      boxSizing: 'border-box',
      background: selected ? BLUE : '#ffffff',
      color: selected ? '#ffffff' : BLUE_TEXT,
      font: `600 12px/${CHIP_HEIGHT - 2}px -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif`,
      whiteSpace: 'nowrap',
      overflow: 'hidden',
      textOverflow: 'ellipsis',
    })
    chip.textContent = label

    return [box, chip]
  }

  /** Bounds of the part on the screen (it may be several elements, e.g. phone and address). */
  protected rectOf(key: string): Rect | null {
    let rect: Rect | null = null

    document.querySelectorAll(selectorOf(key)).forEach((element) => {
      const r = element.getBoundingClientRect()

      if (!r.width && !r.height) {
        return
      }

      rect = rect
        ? {
          top: Math.min(rect.top, r.top),
          left: Math.min(rect.left, r.left),
          right: Math.max(rect.right, r.right),
          bottom: Math.max(rect.bottom, r.bottom),
        }
        : {top: r.top, left: r.left, right: r.right, bottom: r.bottom}
    })

    return rect
  }

  /** The sticky menus and categories of menu pages. */
  protected stickyMenus(): DOMRect | null {
    return document.querySelector(selectorOf('menu-tabs'))?.getBoundingClientRect() ?? null
  }

  protected stickyBottom(): number {
    return Math.max(this.stickyMenus()?.bottom ?? 0, 0)
  }

  /** Scroll to the first of the parts, which is on the page, or open the page of the first one. */
  protected async scrollTo(keys: string[]): Promise<void> {
    let key = keys.find((k) => this.rectOf(k))

    if (!key && keys.length) {
      const target = this.pageOf(keys[0])

      if (!target) {
        return
      }

      this.navigate(target.page, target.hash)
      key = await this.waitFor(keys)
    }

    if (!key) {
      return
    }

    const rect = this.rectOf(key) as Rect
    // the sticky menus stay on top of menu pages
    const offset = (key === 'menu-tabs' ? 0 : (this.stickyMenus()?.height ?? 0)) + 12

    if (rect.top >= offset && rect.bottom <= window.innerHeight - 12) {
      return
    }

    window.scrollTo({top: window.scrollY + rect.top - offset, behavior: 'smooth'})
  }

  protected waitFor(keys: string[]): Promise<string | undefined> {
    const until = Date.now() + WAIT_MS

    return new Promise((resolve) => {
      const check = () => {
        const key = keys.find((k) => this.rectOf(k))

        if (key || Date.now() > until) {
          resolve(key)
        } else {
          setTimeout(check, 50)
        }
      }

      check()
    })
  }

  /** Page, which has the part on it (with the anchor of a category or dish). */
  protected pageOf(key: string): { page: PreviewPage, hash: string } | null {
    const kind = keyKind(key)
    const id = keyId(key)
    const menus: DishMenu[] = this.app.menus ?? []
    const menuOf = (categoryId: number | null) =>
      menus.find((m) => (m.categories ?? []).some((c) => c.id === categoryId))

    if (RESTAURANT_KINDS.includes(kind)) {
      return {page: {page: 'restaurant', menuId: null}, hash: ''}
    }

    let menu: DishMenu | undefined
    let hash = ''

    if (kind === 'menu-tabs') {
      menu = menus[0]
    } else if (kind === 'menu') {
      menu = menus.find((m) => m.id === id)
    } else if (kind === 'category') {
      menu = menuOf(id)
      hash = `#${id}`
    } else if (kind === 'dish') {
      const dish = (this.preview.products ?? []).find((p: Dish) => p.id === id)
      menu = dish ? menuOf(dish.category_id as number) : undefined
      hash = dish ? `#${dish.category_id}-${id}` : ''
    }

    return menu ? {page: {page: 'menu', menuId: menu.id as number}, hash} : null
  }

  protected currentPage(): PreviewPage {
    const match = window.location.pathname.match(/\/menu(?:\/(\d+))?\/?$/)

    return match
      ? {page: 'menu', menuId: match[1] ? parseInt(match[1]) : null}
      : {page: 'restaurant', menuId: null}
  }

  /**
   * Open a page of the restaurant like the browser's back and forward buttons do: the page
   * follows its URL on `popstate`. The entry is replaced, so the editor's history stays as it is.
   */
  protected navigate(page: PreviewPage, hash: string = ''): void {
    const menuId = page.page === 'menu' ? (page.menuId ?? this.app.menus?.[0]?.id ?? null) : null
    const base = window.location.pathname.replace(/\/menu(\/.*)?$/, '')
    const path = menuId ? `${base}/menu/${menuId}` : base

    if (path === window.location.pathname) {
      return
    }

    const state = {
      mode: menuId ? 'menu' : 'restaurant',
      menuId,
      categoryId: null,
      productId: null,
      productPage: false,
      scrollY: 0,
    }

    window.history.replaceState(state, '', path + window.location.search + hash)
    window.dispatchEvent(new PopStateEvent('popstate', {state}))
  }

  /** Report pages opened in the preview, so the editor's page picker follows them. */
  protected watchNavigation(): void {
    const report = () => {
      const page = this.currentPage()

      if (page.page !== this.page.page || page.menuId !== this.page.menuId) {
        this.page = page
        this.post({type: 'editor:navigate', page})
      }

      this.render()
    }

    for (const method of ['pushState', 'replaceState'] as const) {
      const original = window.history[method]

      window.history[method] = function (this: History, ...args: Parameters<History['pushState']>) {
        original.apply(this, args)
        report()
      }
    }

    window.addEventListener('popstate', report)
  }

  /** The same page in another language (its content comes from the server in it). */
  protected switchLocale(locale: string): void {
    const {pathname, search, hash} = window.location

    window.location.replace(`${pathname.replace(/^\/([^\/]+)/, `/${locale}`)}${search}${hash}`)
  }
}

/**
 * Connect the public page to the editor, which shows it in its preview.
 */
export function installPreviewBridge(pinia: Pinia): void {
  new PreviewBridge(useAppStore(pinia), usePreviewStore(pinia)).install()
}
