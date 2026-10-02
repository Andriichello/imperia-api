<script setup lang="ts">
  import {onBeforeUnmount, onMounted, PropType, ref} from 'vue'

  /**
   * A button with a menu under it. The menu closes on a click outside of it (the preview
   * included), on Esc and when one of its items is picked.
   */
  defineProps({
    align: {
      type: String as PropType<'start' | 'end'>,
      default: 'start',
    },
  })

  const open = ref(false)
  const root = ref<HTMLElement | null>(null)

  function toggle() {
    open.value = !open.value
  }

  function close() {
    open.value = false
  }

  function onPointerDown(event: PointerEvent) {
    if (open.value && root.value && !root.value.contains(event.target as Node)) {
      close()
    }
  }

  function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape' && open.value) {
      // the menu closes, not the panel
      event.stopPropagation()
      close()
      root.value?.querySelector<HTMLElement>('[aria-haspopup]')?.focus()
    }
  }

  onMounted(() => {
    document.addEventListener('pointerdown', onPointerDown)
    // focus went to the preview
    window.addEventListener('blur', close)
  })

  onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', onPointerDown)
    window.removeEventListener('blur', close)
  })
</script>

<template>
  <div class="relative"
       ref="root"
       @keydown="onKeydown">
    <slot name="trigger" :open="open" :toggle="toggle"/>

    <div class="e-dropdown absolute top-full mt-1 z-30 max-h-[70vh] overflow-y-auto"
         :class="align === 'end' ? 'right-0' : 'left-0'"
         role="menu"
         v-if="open"
         @click="close">
      <slot/>
    </div>
  </div>
</template>
