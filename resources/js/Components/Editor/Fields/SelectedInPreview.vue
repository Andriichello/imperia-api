<script setup lang="ts">
  import {useI18n} from 'vue-i18n'
  import {MousePointer2} from 'lucide-vue-next'

  /**
   * "Selected in preview" next to the label of the part of a dish picked on its page.
   */
  const {t} = useI18n()
</script>

<template>
  <span class="e-pill ml-auto shrink-0 bg-blue-600 text-white">
    <MousePointer2 class="size-3"/>
    {{ t('editor.dish.selected_in_preview') }}
  </span>
</template>
