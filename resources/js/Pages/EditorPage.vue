<script setup lang="ts">
  import {computed, onBeforeUnmount, onMounted} from 'vue'
  import EditorTopBar from '@/Components/Editor/EditorTopBar.vue'
  import PageStructure from '@/Components/Editor/PageStructure.vue'
  import SectionPanel from '@/Components/Editor/SectionPanel.vue'
  import PreviewPane from '@/Components/Editor/PreviewPane.vue'
  import BrandPanel from '@/Components/Editor/Panels/BrandPanel.vue'
  import DetailsPanel from '@/Components/Editor/Panels/DetailsPanel.vue'
  import HoursPanel from '@/Components/Editor/Panels/HoursPanel.vue'
  import NotesPanel from '@/Components/Editor/Panels/NotesPanel.vue'
  import PhotosPanel from '@/Components/Editor/Panels/PhotosPanel.vue'
  import {useEditorStore} from '@/stores/editor'

  /**
   * The page editor: the top bar, the page structure (or the panel of the part being edited)
   * on the left, and the preview of the public page.
   */
  const editor = useEditorStore()

  /** Panels of the restaurant page's sections. */
  const PANELS = {
    photos: PhotosPanel,
    details: DetailsPanel,
    notes: NotesPanel,
    hours: HoursPanel,
    brand: BrandPanel,
  }

  const panel = computed(() => editor.selection
    ? PANELS[editor.selection.section as keyof typeof PANELS] ?? null
    : null)

  // a panel of its own for each part, so nothing of the previous one stays in it
  const panelKey = computed(() => editor.selection
    ? `${editor.selection.section}:${editor.selection.id ?? ''}`
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

  onMounted(() => {
    window.addEventListener('keydown', onKeydown)
    window.addEventListener('keyup', onKeyup)
    window.addEventListener('blur', onBlur)
  })

  onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown)
    window.removeEventListener('keyup', onKeyup)
    window.removeEventListener('blur', onBlur)
  })
</script>

<template>
  <div class="h-full min-h-[600px] flex flex-col overflow-hidden">
    <EditorTopBar/>

    <div class="flex-1 min-h-0 flex">
      <aside class="w-[420px] shrink-0 flex flex-col bg-white border-r border-zinc-200">
        <component :is="panel"
                   :key="panelKey"
                   v-if="panel"/>

        <SectionPanel :key="panelKey"
                      :selection="editor.selection"
                      v-else-if="editor.selection"/>

        <PageStructure v-else/>
      </aside>

      <PreviewPane/>
    </div>
  </div>
</template>
