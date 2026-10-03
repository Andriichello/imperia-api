<script setup lang="ts">
  import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Eye, X} from 'lucide-vue-next'
  import EditorTopBar from '@/Components/Editor/EditorTopBar.vue'
  import PageStructure from '@/Components/Editor/PageStructure.vue'
  import MissingPanel from '@/Components/Editor/Panels/MissingPanel.vue'
  import PreviewPane from '@/Components/Editor/PreviewPane.vue'
  import ToastStack from '@/Components/Editor/ToastStack.vue'
  import ConfirmDialog from '@/Components/Editor/ConfirmDialog.vue'
  import BrandPanel from '@/Components/Editor/Panels/BrandPanel.vue'
  import DetailsPanel from '@/Components/Editor/Panels/DetailsPanel.vue'
  import HoursPanel from '@/Components/Editor/Panels/HoursPanel.vue'
  import NotesPanel from '@/Components/Editor/Panels/NotesPanel.vue'
  import PhotosPanel from '@/Components/Editor/Panels/PhotosPanel.vue'
  import MenusPanel from '@/Components/Editor/Panels/MenusPanel.vue'
  import MenuPanel from '@/Components/Editor/Panels/MenuPanel.vue'
  import CategoryPanel from '@/Components/Editor/Panels/CategoryPanel.vue'
  import DishPanel from '@/Components/Editor/Panels/DishPanel.vue'
  import {useEditorStore} from '@/stores/editor'

  /**
   * The page editor: the top bar, the page structure (or the panel of the part being edited)
   * on the left, and the preview of the public page.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  // narrow screens: the preview is shown instead of the panel
  const previewOpen = ref(false)

  // a part picked in the preview opens its panel
  watch(() => editor.selection, () => {
    previewOpen.value = false
  })

  /** Panels of the restaurant page's sections, and of menus, categories and dishes. */
  const PANELS = {
    photos: PhotosPanel,
    details: DetailsPanel,
    notes: NotesPanel,
    hours: HoursPanel,
    brand: BrandPanel,
    menus: MenusPanel,
    menu: MenuPanel,
    category: CategoryPanel,
    dish: DishPanel,
  }

  /** Panels of one item (a new one has no id yet). */
  const ITEMS = ['menu', 'category', 'dish']

  const isItem = computed(() => !!editor.selection && ITEMS.includes(editor.selection.section))

  // the item isn't there anymore (e.g. it was deleted)
  const isMissing = computed(() => {
    const selection = editor.selection

    if (!selection || !isItem.value || selection.id === null) {
      return false
    }

    return !{
      menu: editor.findMenu,
      category: editor.findCategory,
      dish: editor.findDish,
    }[selection.section as 'menu' | 'category' | 'dish'](selection.id)
  })

  const panel = computed(() => editor.selection && !isMissing.value
    ? PANELS[editor.selection.section as keyof typeof PANELS] ?? null
    : null)

  // a panel of its own for each part, so nothing of the previous one stays in it
  const panelKey = computed(() => editor.selection
    ? `${editor.selection.section}:${editor.selection.id ?? 'new'}:${editor.selection.parent ?? ''}`
    : null)

  function onKeydown(event: KeyboardEvent) {
    // Select mode browses while Alt (Option) is held
    if (event.key === 'Alt') {
      editor.altHeld = true
      return
    }

    // the panel closes back to the page structure (fields and menus may use Esc themselves)
    if (event.key === 'Escape' && !event.defaultPrevented) {
      editor.close()
    }
  }

  function onKeyup(event: KeyboardEvent) {
    if (event.key === 'Alt') {
      editor.altHeld = false
    }
  }

  // Alt may be released elsewhere
  function onBlur() {
    editor.altHeld = false
  }

  // the browser asks before the page is left (another restaurant, signing out) with unsaved changes
  function onBeforeUnload(event: BeforeUnloadEvent) {
    if (editor.dirty) {
      event.preventDefault()
      event.returnValue = ''
    }
  }

  onMounted(() => {
    window.addEventListener('keydown', onKeydown)
    window.addEventListener('keyup', onKeyup)
    window.addEventListener('blur', onBlur)
    window.addEventListener('beforeunload', onBeforeUnload)
  })

  onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown)
    window.removeEventListener('keyup', onKeyup)
    window.removeEventListener('blur', onBlur)
    window.removeEventListener('beforeunload', onBeforeUnload)
  })
</script>

<template>
  <div class="h-full min-h-[600px] flex flex-col overflow-hidden">
    <EditorTopBar/>

    <div class="flex-1 min-h-0 flex">
      <!-- narrow screens: the panel takes the width, the preview is behind a button -->
      <aside class="w-full lg:w-[420px] shrink-0 flex flex-col bg-white border-r border-zinc-200"
             :class="{'max-lg:hidden': previewOpen}">
        <component :is="panel"
                   :key="panelKey"
                   v-bind="isItem ? {selection: editor.selection} : {}"
                   v-if="panel"/>

        <MissingPanel :key="panelKey"
                      :selection="editor.selection"
                      v-else-if="editor.selection"/>

        <PageStructure v-else/>
      </aside>

      <div class="flex-1 min-w-0 flex"
           :class="{'max-lg:hidden': !previewOpen}">
        <PreviewPane/>
      </div>
    </div>

    <button type="button"
            class="lg:hidden fixed bottom-20 right-5 z-30 e-btn e-btn-primary h-11 px-4 shadow-[0_12px_32px_-8px_rgba(24,24,27,0.5)]"
            @click="previewOpen = !previewOpen">
      <X class="size-4" v-if="previewOpen"/>
      <Eye class="size-4" v-else/>
      {{ previewOpen ? t('editor.narrow.back') : t('editor.narrow.preview') }}
    </button>

    <ToastStack/>
    <ConfirmDialog/>

    <p class="sr-only" aria-live="polite">{{ editor.announcement }}</p>
  </div>
</template>
