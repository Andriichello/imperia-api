<script setup lang="ts">
  import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {EyeOff} from 'lucide-vue-next'
  import PreviewFrame from '@/Components/Editor/PreviewFrame.vue'
  import PreviewToolbar from '@/Components/Editor/PreviewToolbar.vue'
  import {useEditorStore} from '@/stores/editor'
  import type {PreviewPage} from '@/editor/protocol'
  import {isNewId, ITEM_SECTIONS} from '@/editor/sections'

  /**
   * The preview of the public page under its toolbar, which fits itself to the preview's width
   * (it's a container). Brand colors are shown on two pages side by side, the restaurant page
   * and a menu page, when both fit at their full width.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  /** Width of a phone, and the gap between two of them. */
  const PHONE_WIDTH = 390
  const PHONES_GAP = 32

  // the room for the phones
  const body = ref<HTMLElement | null>(null)
  const bodyWidth = ref(0)
  let resizing: ResizeObserver | null = null

  onMounted(() => {
    resizing = new ResizeObserver(([entry]) => bodyWidth.value = entry.contentRect.width)
    resizing.observe(body.value!)
  })

  onBeforeUnmount(() => resizing?.disconnect())

  const twoPages = computed(() => editor.selection?.section === 'brand')

  // the first menu guests can open
  const menuPage = computed<PreviewPage>(() => ({
    page: 'menu',
    menuId: editor.menus.find((menu) => !menu.is_hidden)?.id ?? null,
  }))

  // the menu page is next to the restaurant page (only, when there's room for both)
  const menuPageShown = computed(() => twoPages.value && !!menuPage.value.menuId
    && bodyWidth.value >= 2 * PHONE_WIDTH + PHONES_GAP)

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
  <main class="@container flex-1 min-w-0 flex flex-col bg-[#eef0f3]"
        style="background-image: radial-gradient(#d4d4d8 1px, transparent 1px); background-size: 18px 18px">
    <PreviewToolbar/>

    <div class="relative flex-1 min-h-0 flex justify-center gap-8 px-6 py-4"
         ref="body">
      <p class="absolute top-2 left-1/2 -translate-x-1/2 z-10 max-w-[calc(100%-32px)] inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-zinc-200 text-xs text-zinc-600 shadow-sm"
         role="status"
         v-if="hidden">
        <EyeOff class="size-3.5 shrink-0 text-zinc-400"/>
        <span class="truncate">{{ t('editor.preview.hidden') }}</span>
      </p>

      <div class="min-w-0 min-h-0 flex flex-col items-center">
        <p class="shrink-0 mb-2.5 text-xs font-semibold text-zinc-600"
           v-if="menuPageShown">
          {{ t('editor.toolbar.restaurant_page') }}
        </p>

        <PreviewFrame primary/>
      </div>

      <div class="min-w-0 min-h-0 flex flex-col items-center"
           v-if="menuPageShown">
        <p class="shrink-0 mb-2.5 text-xs font-semibold text-zinc-600">
          {{ t('editor.preview.menu_page') }}
        </p>

        <PreviewFrame :page="menuPage"/>
      </div>
    </div>
  </main>
</template>
