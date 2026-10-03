<script setup lang="ts">
  import {computed, watch} from 'vue'
  import {useI18n} from 'vue-i18n'
  import PreviewFrame from '@/Components/Editor/PreviewFrame.vue'
  import {useEditorStore} from '@/stores/editor'
  import type {PreviewPage} from '@/editor/protocol'
  import type {Selection} from '@/editor/sections'
  import {presetOf} from '@/editor/brand'
  import {translated} from '@/editor/translations'

  /**
   * The preview of the public page with a hint of what's going on. Brand colors are shown
   * on two pages side by side: the restaurant page and a menu page.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const twoPages = computed(() => editor.selection?.section === 'brand')

  // the first menu guests can open
  const menuPage = computed<PreviewPage>(() => ({
    page: 'menu',
    menuId: editor.menus.find((menu) => !menu.is_hidden)?.id ?? null,
  }))

  // side by side with a menu page, the editor's preview shows the restaurant page
  watch(twoPages, (value) => {
    if (value && editor.page.page !== 'restaurant') {
      editor.openPage({page: 'restaurant', menuId: null})
    }
  }, {immediate: true})

  function nameOf(selection: Selection): string {
    const kind = t('editor.sections.' + selection.section)
    const item = {
      menu: () => editor.findMenu(selection.id),
      category: () => editor.findCategory(selection.id),
      dish: () => editor.findDish(selection.id),
    }[selection.section as string]?.()

    return item ? t('editor.preview.item', {kind, name: translated(item.title, editor.defaultLocale)}) : kind
  }

  const hint = computed<{ text: string, dot: string | null }>(() => {
    if (editor.mode === 'browse') {
      return {text: t('editor.preview.hint_browse'), dot: null}
    }

    if (editor.altHeld) {
      return {text: t('editor.preview.hint_alt'), dot: null}
    }

    if (twoPages.value) {
      const colors = editor.previewBrand ?? editor.brand
      const preset = presetOf(colors)
      const name = preset ? t('editor.brand.presets.' + preset.key) : t('editor.brand.custom_colors')

      return {text: t('editor.preview.hint_brand', {name}), dot: colors.primary}
    }

    if (editor.selection) {
      const key = editor.isShown(editor.selection) ? 'editor.preview.hint_editing' : 'editor.preview.hint_hidden'

      return {text: t(key, {name: nameOf(editor.selection)}), dot: '#2563eb'}
    }

    return {text: t('editor.preview.hint_select'), dot: null}
  })
</script>

<template>
  <main class="flex-1 min-w-0 flex flex-col items-center px-6 pt-4 pb-6 bg-[#eef0f3]"
        style="background-image: radial-gradient(#d4d4d8 1px, transparent 1px); background-size: 18px 18px">
    <p class="shrink-0 max-w-full mb-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-zinc-200 text-xs text-zinc-600"
       role="status">
      <span class="size-2 shrink-0"
            :class="twoPages ? 'rounded-full' : 'rounded-[2px]'"
            :style="{background: hint.dot}"
            aria-hidden="true"
            v-if="hint.dot"/>
      <span class="truncate">{{ hint.text }}</span>
    </p>

    <div class="flex-1 min-h-0 w-full flex justify-center gap-8">
      <div class="min-h-0 flex flex-col items-center">
        <p class="shrink-0 mb-2.5 text-xs font-semibold text-zinc-600"
           v-if="twoPages">
          {{ t('editor.top.restaurant_page') }}
        </p>

        <PreviewFrame primary/>
      </div>

      <!-- two phones fit from 1280 px wide -->
      <div class="min-h-0 flex flex-col items-center max-xl:hidden"
           v-if="twoPages && menuPage.menuId">
        <p class="shrink-0 mb-2.5 text-xs font-semibold text-zinc-600">
          {{ t('editor.preview.menu_page') }}
        </p>

        <PreviewFrame :page="menuPage"/>
      </div>
    </div>
  </main>
</template>
