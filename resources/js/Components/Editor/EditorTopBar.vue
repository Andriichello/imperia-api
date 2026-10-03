<script setup lang="ts">
  import {computed} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {
    Check,
    ChevronDown,
    ExternalLink,
    Eye,
    File,
    LayoutDashboard,
    LogOut,
    MousePointer,
    Utensils,
  } from 'lucide-vue-next'
  import type {EditorMenu} from '@/api'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import {useEditorStore} from '@/stores/editor'
  import {INTERFACE_LOCALES, languageName, saveInterfaceLocale, translated} from '@/editor/translations'
  import type {PreviewMode} from '@/editor/protocol'

  const editor = useEditorStore()
  const {t, locale: interfaceLocale} = useI18n()

  /** The editor's own language (the restaurant's content has its own ones). */
  function setInterfaceLocale(locale: string) {
    interfaceLocale.value = locale
    editor.locale = locale
    document.documentElement.lang = locale
    saveInterfaceLocale(locale)
  }

  const restaurantName = computed(() => translated(editor.restaurant?.name, editor.defaultLocale))

  // menus, which guests can open
  const shownMenus = computed<EditorMenu[]>(() => editor.menus.filter((m) => !m.is_hidden))

  const menuTitle = (menu: EditorMenu) => translated(menu.title, editor.defaultLocale)

  const pageLabel = computed(() => {
    const menu = editor.page.page === 'menu' ? editor.findMenu(editor.page.menuId) : null

    return menu
      ? t('editor.top.menu_page', {menu: menuTitle(menu)})
      : t('editor.top.restaurant_page')
  })

  const MODES: { mode: PreviewMode, icon: typeof Eye }[] = [
    {mode: 'select', icon: MousePointer},
    {mode: 'browse', icon: Eye},
  ]

  const initials = computed(() => (editor.user?.name ?? '')
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((word) => word[0].toUpperCase())
    .join('') || '?')

  const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''

  function editorUrl(id: number): string {
    return window.location.pathname.replace(/\/\d+\/?$/, `/${id}`)
  }
</script>

<template>
  <header class="h-14 shrink-0 flex items-center gap-2 lg:gap-3 px-3 lg:px-4 bg-white border-b border-zinc-200">
    <div class="flex items-center gap-2">
      <a class="size-7 rounded-md bg-zinc-900 text-white flex items-center justify-center e-focus"
         :href="editor.urls?.dashboard"
         :aria-label="t('editor.top.home')"
         :title="t('editor.top.home')">
        <Utensils class="size-4"/>
      </a>

      <DropdownMenu v-if="editor.restaurants.length > 1">
        <template #trigger="{open, toggle}">
          <button type="button"
                  class="h-9 inline-flex items-center gap-1.5 px-2 rounded-md text-[15px] font-semibold hover:bg-zinc-100 e-focus"
                  aria-haspopup="menu"
                  :aria-expanded="open"
                  :aria-label="t('editor.top.switch_restaurant')"
                  @click="toggle">
            {{ restaurantName }}
            <ChevronDown class="size-4 text-zinc-500"/>
          </button>
        </template>

        <a class="e-dropdown-item"
           role="menuitem"
           :href="editorUrl(item.id)"
           :aria-current="item.id === editor.restaurant?.id"
           v-for="item in editor.restaurants" :key="item.id">
          <span class="flex-1 truncate">{{ item.name }}</span>
          <Check class="size-4 text-zinc-500" v-if="item.id === editor.restaurant?.id"/>
        </a>
      </DropdownMenu>

      <span class="h-9 inline-flex items-center px-2 text-[15px] font-semibold"
            v-else>
        {{ restaurantName }}
      </span>
    </div>

    <span class="w-px h-6 bg-zinc-200" aria-hidden="true"/>

    <DropdownMenu>
      <template #trigger="{open, toggle}">
        <button type="button"
                class="h-9 inline-flex items-center gap-2 pl-3 pr-2.5 border border-zinc-200 rounded-md bg-white font-medium hover:bg-zinc-50 e-focus"
                aria-haspopup="menu"
                :aria-expanded="open"
                :aria-label="t('editor.top.page')"
                @click="toggle">
          <File class="size-4 text-zinc-500"/>
          <span class="max-w-60 truncate max-md:hidden">{{ pageLabel }}</span>
          <ChevronDown class="size-4 text-zinc-500"/>
        </button>
      </template>

      <button type="button"
              class="e-dropdown-item"
              role="menuitem"
              :aria-current="editor.page.page === 'restaurant'"
              @click="editor.openPage({page: 'restaurant', menuId: null})">
        <span class="flex-1">{{ t('editor.top.restaurant_page') }}</span>
        <Check class="size-4 text-zinc-500" v-if="editor.page.page === 'restaurant'"/>
      </button>

      <button type="button"
              class="e-dropdown-item"
              role="menuitem"
              :aria-current="editor.page.page === 'menu' && editor.page.menuId === menu.id"
              v-for="menu in shownMenus" :key="menu.id"
              @click="editor.openPage({page: 'menu', menuId: menu.id})">
        <span class="flex-1 truncate">{{ t('editor.top.menu_page', {menu: menuTitle(menu)}) }}</span>
        <Check class="size-4 text-zinc-500" v-if="editor.page.page === 'menu' && editor.page.menuId === menu.id"/>
      </button>
    </DropdownMenu>

    <div class="flex gap-0.5 p-[3px] rounded-lg bg-zinc-100"
         role="group"
         :aria-label="t('editor.top.mode')">
      <button type="button"
              class="h-[30px] inline-flex items-center gap-1.5 px-2.5 rounded-md font-semibold e-focus"
              :class="editor.mode === item.mode
                ? 'bg-white text-zinc-900 shadow-[0_1px_2px_rgba(0,0,0,0.08)]'
                : 'text-zinc-600 hover:text-zinc-900'"
              :aria-pressed="editor.mode === item.mode"
              v-for="item in MODES" :key="item.mode"
              @click="editor.mode = item.mode">
        <component :is="item.icon" class="size-[15px]"/>
        <span class="max-sm:sr-only">{{ t('editor.top.' + item.mode) }}</span>
      </button>
    </div>

    <div class="flex-1"/>

    <div class="flex items-center gap-2"
         v-if="editor.locales.length > 1">
      <span class="text-xs text-zinc-500 max-lg:hidden">{{ t('editor.top.preview_in') }}</span>

      <div class="inline-flex p-0.5 rounded-md bg-zinc-100"
           role="group"
           :aria-label="t('editor.top.preview_language')">
        <button type="button"
                class="h-[26px] px-2.5 rounded text-xs font-semibold uppercase e-focus"
                :class="editor.previewLocale === locale
                  ? 'bg-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]'
                  : 'text-zinc-600 hover:text-zinc-900'"
                :aria-pressed="editor.previewLocale === locale"
                v-for="locale in editor.locales" :key="locale"
                @click="editor.previewLocale = locale">
          {{ locale }}
        </button>
      </div>
    </div>

    <button type="button"
            class="e-btn e-btn-secondary"
            @click="editor.select({section: 'brand', id: null})">
      <span class="size-3.5 rounded-full shadow-[inset_0_0_0_1px_rgba(0,0,0,0.1)]"
            :style="{background: editor.brand.primary}"
            aria-hidden="true"/>
      <span class="max-md:sr-only">{{ t('editor.top.brand_colors') }}</span>
    </button>

    <a class="e-btn e-btn-secondary"
       target="_blank"
       rel="noopener"
       :href="editor.restaurant?.url">
      <ExternalLink class="size-[15px]"/>
      <span class="max-md:sr-only">{{ t('editor.top.view_site') }}</span>
    </a>

    <DropdownMenu align="end">
      <template #trigger="{open, toggle}">
        <button type="button"
                class="size-8 rounded-full bg-zinc-200 text-zinc-700 flex items-center justify-center text-xs font-bold e-focus"
                aria-haspopup="menu"
                :aria-expanded="open"
                :aria-label="t('editor.top.account')"
                @click="toggle">
          {{ initials }}
        </button>
      </template>

      <div class="px-2.5 pt-1.5 pb-2 mb-1 border-b border-zinc-100">
        <p class="font-semibold truncate">{{ editor.user?.name }}</p>
        <p class="text-xs text-zinc-500 truncate">{{ editor.user?.email }}</p>
      </div>

      <p class="e-section px-2.5 pt-1.5 pb-1">{{ t('editor.top.interface_language') }}</p>

      <button type="button"
              class="e-dropdown-item"
              role="menuitemradio"
              :aria-checked="editor.locale === locale"
              v-for="locale in INTERFACE_LOCALES" :key="locale"
              @click="setInterfaceLocale(locale)">
        <span class="flex-1" :lang="locale">{{ languageName(locale) }}</span>
        <Check class="size-4 text-zinc-500" v-if="editor.locale === locale"/>
      </button>

      <div class="h-px my-1 bg-[#f0f0f1]"/>

      <a class="e-dropdown-item"
         role="menuitem"
         :href="editor.urls.panel"
         v-if="editor.urls?.panel">
        <LayoutDashboard class="size-4 text-zinc-500"/>
        {{ t('editor.top.admin_panel') }}
      </a>

      <form method="post"
            :action="editor.urls?.logout">
        <input type="hidden" name="_token" :value="csrfToken"/>

        <button type="submit"
                class="e-dropdown-item"
                role="menuitem">
          <LogOut class="size-4 text-zinc-500"/>
          {{ t('editor.top.sign_out') }}
        </button>
      </form>
    </DropdownMenu>
  </header>
</template>
