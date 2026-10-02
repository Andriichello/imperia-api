<script setup lang="ts">
  import {PropType} from 'vue'
  import {useI18n} from 'vue-i18n'

  /**
   * Label of a field, with the language of a translated one ("Restaurant name EN").
   */
  defineProps({
    // id of the field
    target: {
      type: String as PropType<string | null>,
      default: null,
    },
    label: {
      type: String,
      required: true,
    },
    // the language of the text, for translated fields
    locale: {
      type: String as PropType<string | null>,
      default: null,
    },
  })

  const {t} = useI18n()
</script>

<template>
  <div class="flex items-center gap-1.5">
    <label class="e-label"
           :for="target ?? undefined">
      {{ label }}
    </label>

    <span class="e-locale-chip"
          :title="t('editor.fields.translated')"
          v-if="locale">
      {{ locale }}
    </span>

    <slot/>
  </div>
</template>
