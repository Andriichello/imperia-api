<script setup lang="ts">
  import {useI18n} from 'vue-i18n'

  /**
   * "Unsaved" next to an item with a draft, in the editor's lists.
   */
  const {t} = useI18n()
</script>

<template>
  <span class="e-pill shrink-0 bg-[#fffbeb] text-[#92400e] shadow-[inset_0_0_0_1px_#fde68a]">
    <span class="size-1.5 rounded-full bg-amber-500" aria-hidden="true"/>
    {{ t('editor.save.unsaved') }}
  </span>
</template>
