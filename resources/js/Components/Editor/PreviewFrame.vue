<script setup lang="ts">
  import {onBeforeUnmount, PropType, ref, watch} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {useEditorStore} from '@/stores/editor'
  import {usePreviewBridge} from '@/composables/usePreviewBridge'
  import type {PreviewMessage, PreviewPage} from '@/editor/protocol'
  import {DISH_FIELDS, keysOf} from '@/editor/sections'

  /**
   * The public page in a phone-sized frame: it's the real page (`?editor=1`), which reports
   * the parts clicked in it and outlines the ones hovered and edited (see `previewBridge.ts`).
   * It shows the drafts too, outlined in amber.
   */
  const props = defineProps({
    // the editor's preview: its page follows the page picker and the page structure
    primary: {
      type: Boolean,
      default: false,
    },
    // the page of another preview (e.g. the menu page next to the restaurant one)
    page: {
      type: Object as PropType<PreviewPage | null>,
      default: null,
    },
  })

  const editor = useEditorStore()
  const {t} = useI18n()

  const frame = ref<HTMLIFrameElement | null>(null)

  /** Kinds of the parts of the page, named on their outlines. */
  const KINDS = ['photos', 'details', 'contact', 'notes', 'menus', 'menu-tabs', 'hours', 'menu', 'category', 'dish']

  /** How long the preview waits for typing to stop before it shows the changes. */
  const DRAFT_DELAY = 150

  function pageUrl(locale: string, page: PreviewPage): string {
    const base = `/${locale}/web/${editor.restaurant!.id}`

    if (page.page === 'restaurant' || !page.menuId) {
      return `${base}?editor=1`
    }

    // a dish page is the dish's drawer on its menu page
    const dish = page.page === 'dish' ? editor.findDish(page.dishId ?? null) : null
    const hash = dish ? `#${dish.category_id}-${dish.id}-page` : ''

    return `${base}/menu/${page.menuId}?editor=1${hash}`
  }

  // the page loaded in the frame: the next ones are opened inside it, without loading it again
  const src = ref(pageUrl(editor.previewLocale, props.page ?? editor.page))
  // language of the loaded page
  let loadedLocale = editor.previewLocale
  // the page listens to messages
  let ready = false
  // parts to show, asked for while the page was loading
  let pendingReveal: string[] | null = null
  let draftTimer: ReturnType<typeof setTimeout> | null = null

  const {post} = usePreviewBridge(frame, onMessage)

  function onMessage(message: PreviewMessage) {
    switch (message.type) {
      case 'editor:ready':
        ready = true
        loadedLocale = message.locale

        if (props.primary) {
          editor.previewLocale = message.locale
          editor.page = message.page
        }

        sync()
        break
      case 'editor:select':
        editor.selectKey(message.key)
        break
      case 'editor:hover':
        editor.previewHover = message.key
        break
      case 'editor:navigate':
        if (props.primary) {
          editor.page = message.page
        }
        break
      case 'editor:alt':
        editor.altHeld = message.held
        break
      case 'editor:escape':
        editor.close()
        break
    }
  }

  function postSelection() {
    post({type: 'editor:select', keys: keysOf(editor.selection), label: null})
  }

  function postDraft() {
    post({type: 'editor:draft', patch: editor.previewPatch})
    post({type: 'editor:unsaved', keys: editor.unsavedKeys})
  }

  function postBrand() {
    post({type: 'editor:brand', brand: editor.previewBrand ?? editor.brand})
  }

  /** Everything the page has to know, once it's loaded. */
  function sync() {
    post({
      type: 'editor:labels',
      labels: {
        ...Object.fromEntries(KINDS.map((kind) => [kind, t('editor.sections.' + kind)])),
        ...Object.fromEntries(DISH_FIELDS.map((field) => [`dish-${field}`, t('editor.dish_fields.' + field)])),
      },
      hover: t('editor.preview.click_to_edit', {label: '{label}'}),
      unsaved: t('editor.preview.unsaved', {label: '{label}'}),
    })
    post({type: 'editor:mode', mode: editor.mode})
    post({type: 'editor:alt', held: editor.altHeld})
    postSelection()
    postDraft()
    postBrand()

    if (pendingReveal) {
      post({type: 'editor:scrollTo', keys: pendingReveal})
      pendingReveal = null
    }
  }

  watch(() => editor.mode, (mode) => post({type: 'editor:mode', mode}))

  watch(() => editor.altHeld, (held) => post({type: 'editor:alt', held}))

  watch(() => editor.selection, postSelection)

  watch(() => editor.treeHover, (selection) => post({type: 'editor:hover', keys: keysOf(selection)}))

  // typing shows in the preview once it pauses
  watch(() => [editor.previewPatch, editor.unsavedKeys], () => {
    if (draftTimer) {
      clearTimeout(draftTimer)
    }

    draftTimer = setTimeout(postDraft, DRAFT_DELAY)
  }, {deep: true})

  watch(() => [editor.previewBrand, editor.brand], postBrand, {deep: true})

  if (props.primary) {
    // a part asked for before the preview was there (a link to it)
    if (editor.reveal.count) {
      pendingReveal = editor.reveal.keys
    }

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
  }

  // the page comes in the language from the server: it's loaded again
  watch(() => editor.previewLocale, (locale) => {
    if (locale === loadedLocale) {
      return
    }

    if (ready) {
      post({type: 'editor:locale', locale})
    } else {
      src.value = pageUrl(locale, props.page ?? editor.page)
    }

    loadedLocale = locale
    ready = false
  })

  onBeforeUnmount(() => {
    if (draftTimer) {
      clearTimeout(draftTimer)
    }
  })
</script>

<template>
  <div class="w-[390px] flex-1 min-h-0 overflow-hidden bg-white shadow-[0_0_0_1px_#d4d4d8,0_16px_40px_-20px_rgba(24,24,27,0.4)]">
    <iframe class="block w-full h-full border-0"
            ref="frame"
            :src="src"
            :title="t('editor.preview.title')"/>
  </div>
</template>
