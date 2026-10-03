<script setup lang="ts">
  import {computed, nextTick, onMounted, PropType, ref, watch} from 'vue'
  import type {DishField} from '@/editor/sections'
  import {useEditorStore} from '@/stores/editor'

  /**
   * A part of the dish panel, which can be picked on the dish's page in the preview: once it's
   * picked, the panel scrolls to it, its first field is focused, and it's outlined in blue
   * (`selected` of its slot shows "Selected in preview" next to its label).
   */
  const props = defineProps({
    field: {
      type: String as PropType<DishField>,
      required: true,
    },
  })

  const editor = useEditorStore()

  const root = ref<HTMLElement | null>(null)

  const selected = computed(() => editor.selection?.field === props.field)

  function show() {
    if (!selected.value) {
      return
    }

    nextTick(() => {
      root.value?.scrollIntoView({block: 'start', behavior: 'smooth'})
      root.value?.querySelector<HTMLElement>('input:not([type="file"]), textarea, select, button')
        ?.focus({preventScroll: true})
    })
  }

  // picked again: shown again
  watch(() => editor.fieldPicks, show)

  // picked while another dish was open
  onMounted(show)
</script>

<template>
  <div class="scroll-mt-5 rounded-md outline-offset-[6px]"
       :class="{'outline-2 outline-blue-600': selected}"
       :data-field="field"
       ref="root">
    <slot :selected="selected"/>
  </div>
</template>
