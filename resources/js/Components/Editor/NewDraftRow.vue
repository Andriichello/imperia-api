<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {ChevronRight} from 'lucide-vue-next'
  import UnsavedPill from '@/Components/Editor/UnsavedPill.vue'
  import type {DraftEntry} from '@/editor/items'
  import {translated, Translations} from '@/editor/translations'
  import {useEditorStore} from '@/stores/editor'

  /**
   * A new menu, category or dish in its list, till it's saved: a click opens it again.
   */
  const props = defineProps({
    entry: {
      type: Object as PropType<DraftEntry>,
      required: true,
    },
  })

  const editor = useEditorStore()
  const {t} = useI18n()

  const name = computed(() => translated((props.entry.values as { title: Translations }).title, editor.defaultLocale)
    || t(`editor.${props.entry.selection.section}.new`))
</script>

<template>
  <button type="button"
          class="min-h-10 w-full flex items-center gap-2 px-2.5 rounded-md text-start hover:bg-zinc-50 e-focus"
          @click="editor.select(entry.selection, true)">
    <span class="flex-1 min-w-0 truncate font-semibold">{{ name }}</span>
    <span class="e-pill bg-green-100 text-green-800">{{ t('editor.save.new') }}</span>
    <UnsavedPill/>
    <ChevronRight class="size-4 shrink-0 text-zinc-400"/>
  </button>
</template>
