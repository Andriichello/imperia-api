<script setup lang="ts">
  import {computed, ref, watch} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {useEditorStore} from '@/stores/editor'
  import {usePreviewBridge} from '@/composables/usePreviewBridge'
  import type {PreviewMessage, PreviewPage} from '@/editor/protocol'
  import {keysOf, Selection} from '@/editor/sections'
  import {translated} from '@/editor/translations'

  /**
   * The public page in a phone-sized frame: it's the real page (`?editor=1`), which reports the
   * parts clicked in it and outlines the ones hovered and edited (see `previewBridge.ts`).
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const frame = ref<HTMLIFrameElement | null>(null)

  /** Kinds of the parts of the page, named on their outlines. */
  const KINDS = ['photos', 'details', 'contact', 'notes', 'menus', 'menu-tabs', 'hours', 'menu', 'category', 'dish']

  function pageUrl(locale: string, page: PreviewPage): string {
    const base = `/${locale}/web/${editor.restaurant!.id}`

    return (page.page === 'menu' && page.menuId ? `${base}/menu/${page.menuId}` : base) + '?editor=1'
  }

  // the page loaded in the frame: the next ones are opened inside it, without loading it again
  const src = ref(pageUrl(editor.previewLocale, editor.page))
  // language of the loaded page
  let loadedLocale = editor.previewLocale
  // the page listens to messages
  let ready = false
  // parts to show, asked for while the page was loading
  let pendingReveal: string[] | null = null

  const {post} = usePreviewBridge(frame, onMessage)

  function onMessage(message: PreviewMessage) {
    switch (message.type) {
      case 'editor:ready':
        ready = true
        loadedLocale = message.locale
        editor.previewLocale = message.locale
        editor.page = message.page
        sync()
        break
      case 'editor:select':
        editor.selectKey(message.key)
        break
      case 'editor:hover':
        editor.previewHover = message.key
        break
      case 'editor:navigate':
        editor.page = message.page
        break
      case 'editor:alt':
        editor.altHeld = message.held
        break
      case 'editor:escape':
        editor.close()
        break
    }
  }

  /** Everything the page has to know, once it's loaded. */
  function sync() {
    post({
      type: 'editor:labels',
      labels: Object.fromEntries(KINDS.map((kind) => [kind, t('editor.sections.' + kind)])),
      hover: t('editor.preview.click_to_edit', {label: '{label}'}),
    })
    post({type: 'editor:mode', mode: editor.mode})
    post({type: 'editor:alt', held: editor.altHeld})
    post({type: 'editor:select', keys: keysOf(editor.selection), label: null})

    if (pendingReveal) {
      post({type: 'editor:scrollTo', keys: pendingReveal})
      pendingReveal = null
    }
  }

  watch(() => editor.mode, (mode) => post({type: 'editor:mode', mode}))

  watch(() => editor.altHeld, (held) => post({type: 'editor:alt', held}))

  watch(() => editor.selection, (selection) => post({type: 'editor:select', keys: keysOf(selection), label: null}))

  watch(() => editor.treeHover, (selection) => post({type: 'editor:hover', keys: keysOf(selection)}))

  watch(() => editor.reveal.count, () => {
    if (ready) {
      post({type: 'editor:scrollTo', keys: editor.reveal.keys})
    } else {
      pendingReveal = editor.reveal.keys
    }
  })

  watch(() => editor.navigation.count, () => {
    if (ready) {
      post({type: 'editor:navigate', page: editor.navigation.page})
    } else {
      src.value = pageUrl(loadedLocale, editor.navigation.page)
    }
  })

  // the page comes in the language from the server: it's loaded again
  watch(() => editor.previewLocale, (locale) => {
    if (locale === loadedLocale) {
      return
    }

    if (ready) {
      post({type: 'editor:locale', locale})
    } else {
      src.value = pageUrl(locale, editor.page)
    }

    loadedLocale = locale
    ready = false
  })

  function nameOf(selection: Selection): string {
    const kind = t('editor.sections.' + selection.section)
    const item = {
      menu: () => editor.findMenu(selection.id),
      category: () => editor.findCategory(selection.id),
      dish: () => editor.findDish(selection.id),
    }[selection.section as string]?.()

    return item ? t('editor.preview.item', {kind, name: translated(item.title, editor.defaultLocale)}) : kind
  }

  const hint = computed<{ text: string, editing: boolean }>(() => {
    if (editor.mode === 'browse') {
      return {text: t('editor.preview.hint_browse'), editing: false}
    }

    if (editor.altHeld) {
      return {text: t('editor.preview.hint_alt'), editing: false}
    }

    if (editor.selection) {
      const key = editor.isShown(editor.selection) ? 'editor.preview.hint_editing' : 'editor.preview.hint_hidden'

      return {text: t(key, {name: nameOf(editor.selection)}), editing: true}
    }

    return {text: t('editor.preview.hint_select'), editing: false}
  })
</script>

<template>
  <main class="flex-1 min-w-0 flex flex-col items-center px-6 pt-4 pb-6 bg-[#eef0f3]"
        style="background-image: radial-gradient(#d4d4d8 1px, transparent 1px); background-size: 18px 18px">
    <p class="shrink-0 max-w-full mb-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-zinc-200 text-xs text-zinc-600"
       role="status">
      <span class="size-2 shrink-0 rounded-[2px] bg-blue-600"
            aria-hidden="true"
            v-if="hint.editing"/>
      <span class="truncate">{{ hint.text }}</span>
    </p>

    <div class="w-[390px] flex-1 min-h-0 rounded-[28px] overflow-hidden bg-white shadow-[0_0_0_1px_#d4d4d8,0_24px_48px_-16px_rgba(24,24,27,0.35)]">
      <iframe class="block w-full h-full border-0"
              ref="frame"
              :src="src"
              :title="t('editor.preview.title')"/>
    </div>
  </main>
</template>
