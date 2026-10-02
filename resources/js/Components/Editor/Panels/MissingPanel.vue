<script setup lang="ts">
  import {PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import InfoBox from '@/Components/Editor/Fields/InfoBox.vue'
  import type {Selection} from '@/editor/sections'
  import {useEditorStore} from '@/stores/editor'

  /**
   * A menu, category or dish, which isn't there anymore (e.g. it was deleted meanwhile).
   */
  defineProps({
    selection: {
      type: Object as PropType<Selection>,
      required: true,
    },
  })

  const editor = useEditorStore()
  const {t} = useI18n()
</script>

<template>
  <PanelShell :breadcrumbs="[{label: t('editor.panel.page_structure'), selection: null}]"
              :title="t('editor.sections.' + selection.section)"
              @navigate="editor.close()"
              @close="editor.close()">
    <InfoBox>{{ t('editor.panel.not_found') }}</InfoBox>
  </PanelShell>
</template>
