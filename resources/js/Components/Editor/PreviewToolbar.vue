<script setup lang="ts">
  import {computed} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Check, ChevronDown, Eye, File, MousePointer2, Palette} from 'lucide-vue-next'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import type {EditorDish, EditorMenu} from '@/api'
  import type {PreviewMode, PreviewPage} from '@/editor/protocol'
  import {translated} from '@/editor/translations'
  import {useEditorStore} from '@/stores/editor'

  /**
   * The toolbar above the preview: the page it shows (the restaurant page, a menu, the dish
   * page of the dish being edited), Select / Browse (hold Alt to browse for a moment), the
   * preview's language and the brand colors.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const name = (item: EditorMenu | EditorDish) => translated(item.title, editor.defaultLocale)

  // menus, which guests can open
  const menus = computed<EditorMenu[]>(() => editor.menus.filter((menu) => !menu.is_hidden))

  /** Dish pages it can show: of the dish being edited, and the one it shows (opened in Browse). */
  const dishes = computed<EditorDish[]>(() => {
    const ids = [
      editor.selection?.section === 'dish' ? editor.selection.id : null,
      editor.page.page === 'dish' ? editor.page.dishId : null,
    ]

    return [...new Set(ids)]
      .filter((id): id is number => !!id && editor.isShown({section: 'dish', id}))
      .map((id) => editor.findDish(id)!)
  })

  function labelOf(page: PreviewPage): string {
    if (page.page === 'dish') {
      const dish = editor.findDish(page.dishId ?? null)

      return dish ? t('editor.toolbar.dish_page', {dish: name(dish)}) : t('editor.toolbar.restaurant_page')
    }

    const menu = page.page === 'menu' ? editor.findMenu(page.menuId) : null

    return menu ? t('editor.toolbar.menu_page', {menu: name(menu)}) : t('editor.toolbar.restaurant_page')
  }

  const isCurrent = (page: PreviewPage) => page.page === editor.page.page
    && (page.page === 'restaurant' || (page.page === 'menu' ? page.menuId === editor.page.menuId : page.dishId === editor.page.dishId))

  const pages = computed<PreviewPage[]>(() => [
    {page: 'restaurant', menuId: null},
    ...menus.value.map((menu) => ({page: 'menu' as const, menuId: menu.id})),
    ...dishes.value.map((dish) => ({page: 'dish' as const, menuId: dish.menu_id, dishId: dish.id})),
  ])

  const MODES: { mode: PreviewMode, icon: typeof Eye }[] = [
    {mode: 'select', icon: MousePointer2},
    {mode: 'browse', icon: Eye},
  ]
</script>

<template>
  <div class="h-12 shrink-0 grid grid-cols-[1fr_auto_1fr] items-center gap-3 px-3.5 bg-white border-b border-zinc-200">
    <div class="min-w-0 flex">
      <DropdownMenu>
        <template #trigger="{open, toggle}">
          <button type="button"
                  class="e-tb max-w-full"
                  aria-haspopup="menu"
                  :aria-expanded="open"
                  :aria-label="t('editor.toolbar.page', {page: labelOf(editor.page)})"
                  @click="toggle">
            <File class="size-[15px] shrink-0 text-zinc-500"/>
            <span class="truncate">{{ labelOf(editor.page) }}</span>
            <ChevronDown class="size-3.5 shrink-0 text-zinc-500"/>
          </button>
        </template>

        <button type="button"
                class="e-dropdown-item"
                role="menuitem"
                :aria-current="isCurrent(page)"
                v-for="page in pages" :key="`${page.page}:${page.menuId}:${page.dishId}`"
                @click="editor.openPage(page)">
          <span class="flex-1 truncate">{{ labelOf(page) }}</span>
          <Check class="size-4 text-zinc-500" v-if="isCurrent(page)"/>
        </button>
      </DropdownMenu>
    </div>

    <div class="e-seg"
         role="group"
         :aria-label="t('editor.toolbar.mode')">
      <button type="button"
              class="e-focus"
              :aria-pressed="editor.mode === item.mode"
              :title="t('editor.toolbar.' + item.mode + '_help')"
              v-for="item in MODES" :key="item.mode"
              @click="editor.mode = item.mode">
        <component :is="item.icon" class="size-3.5"/>
        <span class="max-xl:sr-only">{{ t('editor.toolbar.' + item.mode) }}</span>
        <kbd class="e-kbd max-xl:hidden" v-if="item.mode === 'browse'">Alt</kbd>
      </button>
    </div>

    <div class="justify-self-end flex items-center gap-2.5">
      <div class="flex items-center gap-1.5"
           role="group"
           :aria-label="t('editor.toolbar.preview_language')"
           v-if="editor.locales.length > 1">
        <span class="text-xs text-zinc-500 max-xl:hidden">{{ t('editor.toolbar.preview') }}</span>

        <div class="e-seg">
          <button type="button"
                  class="px-2! uppercase e-focus"
                  :aria-pressed="editor.previewLocale === locale"
                  v-for="locale in editor.locales" :key="locale"
                  @click="editor.previewLocale = locale">
            {{ locale }}
          </button>
        </div>
      </div>

      <span class="w-px h-5 bg-zinc-200" aria-hidden="true" v-if="editor.locales.length > 1"/>

      <button type="button"
              class="e-tb"
              :title="t('editor.sections.brand')"
              @click="editor.select({section: 'brand', id: null})">
        <Palette class="size-[15px] text-zinc-600"/>
        <span class="size-3 rounded-full shadow-[inset_0_0_0_1px_rgba(0,0,0,0.12)]"
              :style="{background: (editor.previewBrand ?? editor.brand).primary}"
              aria-hidden="true"/>
        <span class="max-xl:sr-only">{{ t('editor.toolbar.colors') }}</span>
      </button>
    </div>
  </div>
</template>
