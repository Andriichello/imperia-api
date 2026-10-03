<script setup lang="ts">
  import {nextTick, PropType, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {GripVertical} from 'lucide-vue-next'
  import {useEditorStore} from '@/stores/editor'

  /**
   * The handle to drag an item of a list by, which moves it with the arrow keys too
   * (Home and End: to the start and the end).
   */
  const props = defineProps({
    // of the item, for screen readers
    name: {
      type: String,
      default: '',
    },
    index: {
      type: Number,
      required: true,
    },
    count: {
      type: Number,
      required: true,
    },
    // items in a row of a grid: up and down move by a row
    columns: {
      type: Number,
      default: 1,
    },
    iconClass: {
      type: String as PropType<string>,
      default: 'size-4',
    },
  })

  const emits = defineEmits<{
    (e: 'move', from: number, to: number): void
  }>()

  const editor = useEditorStore()
  const {t} = useI18n()

  const button = ref<HTMLButtonElement | null>(null)

  function onKeydown(event: KeyboardEvent) {
    const steps: Record<string, number> = {
      ArrowUp: -props.columns,
      ArrowDown: props.columns,
      ArrowLeft: -1,
      ArrowRight: 1,
      Home: -props.count,
      End: props.count,
    }

    if (!(event.key in steps)) {
      return
    }

    event.preventDefault()

    const to = Math.min(Math.max(props.index + steps[event.key], 0), props.count - 1)

    if (to === props.index) {
      return
    }

    emits('move', props.index, to)
    editor.announce(t('editor.reorder.moved', {position: to + 1, count: props.count}))

    // it's moved in the page, so it loses the focus
    nextTick(() => button.value?.focus())
  }
</script>

<template>
  <button type="button"
          class="e-grip e-focus"
          ref="button"
          :aria-label="t('editor.reorder.label', {name})"
          :title="t('editor.reorder.hint')"
          @keydown="onKeydown">
    <GripVertical :class="iconClass"/>
  </button>
</template>
