<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {CalendarClock} from 'lucide-vue-next'
  import {formatDateTime} from '@/admin/format'
  import {describePlanned, plannedFor} from '@/editor/planned'
  import type {Selection} from '@/editor/sections'
  import {useEditorStore} from '@/stores/editor'

  /**
   * "1 scheduled change for this dish: archive size 450 g on Sun 1 Nov, 00:00. Manage": what's
   * planned for the menu, category or dish in scheduled versions.
   */
  const props = defineProps({
    selection: {
      type: Object as PropType<Selection>,
      required: true,
    },
  })

  const editor = useEditorStore()
  const {t, locale} = useI18n()

  const planned = computed(() => editor.restaurant ? plannedFor(editor.versions, props.selection, editor.restaurant) : [])

  const text = computed(() => {
    const [first] = planned.value
    const kind = props.selection.section
    const date = formatDateTime(first.goesLiveAt, locale.value)

    return planned.value.length === 1
      ? t(`editor.planned.${kind}_one`, {
        what: describePlanned(first.change, t, editor.restaurant?.currency ?? 'uah'),
        date,
      })
      : t(`editor.planned.${kind}_many`, {count: planned.value.length, date})
  })
</script>

<template>
  <div class="shrink-0 flex gap-2.5 mx-5 mt-3 px-3 py-2.5 rounded-lg bg-blue-50 border border-blue-200 text-[13px]/[18px] text-blue-900"
       role="status"
       v-if="planned.length">
    <CalendarClock class="size-4 shrink-0 mt-px"/>
    <p class="flex-1">
      {{ text }}
      <a class="font-semibold underline rounded e-focus"
         :href="editor.versionUrl(planned[0].version.id)">
        {{ t('editor.planned.manage') }}
      </a>
    </p>
  </div>
</template>
