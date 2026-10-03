<script setup lang="ts">
  import {computed, watch} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {EyeOff} from 'lucide-vue-next'
  import PreviewFrame from '@/Components/Editor/PreviewFrame.vue'
  import PreviewToolbar from '@/Components/Editor/PreviewToolbar.vue'
  import {useEditorStore} from '@/stores/editor'
  import type {PreviewPage} from '@/editor/protocol'
  import {isNewId, ITEM_SECTIONS} from '@/editor/sections'

  /**
   * The preview of the public page under its toolbar. Brand colors are shown on two pages side
   * by side: the restaurant page and a menu page.
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

  // the menu, category or dish being edited isn't on the page: guests don't see it
  const hidden = computed(() => {
    const selection = editor.selection

    return !!selection && ITEM_SECTIONS.includes(selection.section) && !isNewId(selection.id)
      && !editor.isShown(selection)
  })
</script>

<template>
  <main class="flex-1 min-w-0 flex flex-col bg-[#eef0f3]"
        style="background-image: radial-gradient(#d4d4d8 1px, transparent 1px); background-size: 18px 18px">
    <PreviewToolbar/>

    <div class="relative flex-1 min-h-0 flex justify-center gap-8 px-6 py-4">
      <p class="absolute top-2 left-1/2 -translate-x-1/2 z-10 max-w-[calc(100%-32px)] inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-zinc-200 text-xs text-zinc-600 shadow-sm"
         role="status"
         v-if="hidden">
        <EyeOff class="size-3.5 shrink-0 text-zinc-400"/>
        <span class="truncate">{{ t('editor.preview.hidden') }}</span>
      </p>

      <div class="min-h-0 flex flex-col items-center">
        <p class="shrink-0 mb-2.5 text-xs font-semibold text-zinc-600"
           v-if="twoPages">
          {{ t('editor.toolbar.restaurant_page') }}
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
