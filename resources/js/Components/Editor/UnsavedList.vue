<script setup lang="ts">
  import {computed, nextTick, onMounted, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {BookOpen, CalendarClock, Image, Info, List, MessageSquare, Palette, Search, Soup, Undo2} from 'lucide-vue-next'
  import {describeDraft, DraftSummary} from '@/editor/changes'
  import {KINDS} from '@/editor/items'
  import {draftKey, Section} from '@/editor/sections'
  import {useEditorStore} from '@/stores/editor'

  /**
   * The list of unsaved items, above the save bar: grouped by menu › category (the restaurant
   * page's sections last), with what each one changes. An item opens its panel, its revert
   * button discards its changes.
   */
  const editor = useEditorStore()
  const {t, locale} = useI18n()

  const query = ref('')
  const search = ref<HTMLInputElement | null>(null)

  const ICONS: Record<Section, typeof Image> = {
    photos: Image,
    details: Info,
    notes: MessageSquare,
    hours: CalendarClock,
    brand: Palette,
    menus: BookOpen,
    menu: BookOpen,
    category: List,
    dish: Soup,
  }

  const items = computed<DraftSummary[]>(() => editor.unsaved
    .map((entry) => describeDraft(entry, {t, restaurant: editor.restaurant!, locale: locale.value}))
    .sort((a, b) => a.group.order - b.group.order || a.order - b.order))

  const groups = computed(() => {
    const words = query.value.trim().toLowerCase()
    const matching = items.value.filter((item) => !words
      || `${item.name} ${item.summary} ${item.group.label}`.toLowerCase().includes(words))
    const groups: { key: string, label: string, items: DraftSummary[] }[] = []

    for (const item of matching) {
      const group = groups.find((other) => other.key === item.group.key)

      if (group) {
        group.items.push(item)
      } else {
        groups.push({key: item.group.key, label: item.group.label, items: [item]})
      }
    }

    return groups
  })

  const openKey = computed(() => editor.selection ? draftKey(editor.selection) : null)

  /** It wasn't saved: its mistakes, or another reason, or it can't be saved as it is. */
  function problemOf(item: DraftSummary): string | null {
    const entry = item.entry

    if (entry.failed) {
      return t('editor.unsaved.failed')
    }

    if (Object.keys(entry.errors).length
      || !KINDS[entry.selection.section].valid(entry.values, editor.restaurant!, entry.selection)) {
      return t('editor.unsaved.needs_fixing')
    }

    return null
  }

  function open(item: DraftSummary) {
    editor.select(item.entry.selection, true)
  }

  function onKeydown(event: KeyboardEvent) {
    // the list closes, the panel stays
    if (event.key === 'Escape') {
      event.preventDefault()
      event.stopPropagation()
      editor.reviewOpen = false
    }
  }

  onMounted(() => nextTick(() => search.value?.focus()))
</script>

<template>
  <div class="absolute left-3 right-3 bottom-16 z-20 max-h-[min(500px,calc(100vh-190px))] flex flex-col overflow-hidden rounded-[10px] bg-white border border-zinc-200 shadow-[0_20px_48px_-12px_rgba(24,24,27,0.35)]"
       role="dialog"
       :aria-label="t('editor.unsaved.title', editor.unsaved.length)"
       @keydown="onKeydown">
    <div class="shrink-0 flex items-center gap-2 px-3.5 pt-3 pb-2.5">
      <p class="shrink-0 font-semibold">{{ t('editor.unsaved.title', editor.unsaved.length) }}</p>
      <span class="e-help ml-auto text-end">{{ t('editor.unsaved.kept') }}</span>
    </div>

    <div class="shrink-0 relative px-3.5 pb-2.5">
      <Search class="absolute left-6 top-2.5 size-3.5 text-zinc-400" aria-hidden="true"/>
      <input class="e-input h-[34px] pl-8"
             type="search"
             ref="search"
             :placeholder="t('editor.unsaved.find')"
             :aria-label="t('editor.unsaved.find_label')"
             v-model="query"/>
    </div>

    <div class="flex-1 min-h-0 overflow-y-auto">
      <p class="px-3.5 py-4 text-[13px] text-zinc-500"
         v-if="!groups.length">
        {{ t('editor.unsaved.nothing_found') }}
      </p>

      <div v-for="group in groups" :key="group.key">
        <p class="sticky top-0 z-[1] flex gap-2 px-3.5 py-[5px] bg-zinc-100 text-[11px]/4 font-bold tracking-[0.04em] uppercase text-zinc-500">
          <span class="flex-1 truncate">{{ group.label }}</span>
          <span>{{ group.items.length }}</span>
        </p>

        <div class="min-h-11 flex items-center gap-2.5 py-[5px] pl-3.5 pr-2 border-t border-zinc-100"
             :class="{'bg-blue-50': item.entry.key === openKey}"
             v-for="item in group.items" :key="item.entry.key">
          <component :is="ICONS[item.entry.selection.section]"
                     class="size-4 shrink-0 text-zinc-500"
                     aria-hidden="true"/>

          <button type="button"
                  class="flex-1 min-w-0 flex flex-col text-start rounded e-focus"
                  @click="open(item)">
            <span class="font-semibold truncate">
              {{ item.name }}
              <span class="e-help font-normal" v-if="item.entry.key === openKey">· {{ t('editor.unsaved.open_now') }}</span>
            </span>

            <span class="text-xs text-zinc-500 truncate">
              <span class="text-red-700" v-if="problemOf(item)">{{ problemOf(item) }} · </span>
              {{ item.summary }}
            </span>
          </button>

          <button type="button"
                  class="e-icon-btn size-7"
                  :aria-label="t('editor.unsaved.revert', {name: item.name})"
                  :title="t('editor.unsaved.revert_short')"
                  @click="editor.revert(item.entry.key, item.name)">
            <Undo2 class="size-4"/>
          </button>
        </div>
      </div>
    </div>

    <div class="shrink-0 flex items-center gap-2.5 px-3.5 py-2.5 border-t border-zinc-200 bg-zinc-50"
         v-if="editor.schedulable.length">
      <p class="flex-1 text-xs/4 text-zinc-600">{{ t('editor.unsaved.big_update') }}</p>

      <button type="button"
              class="e-btn e-btn-secondary h-[30px] px-2.5 text-[13px]"
              @click="editor.reviewOpen = false; editor.scheduleOpen = true">
        <CalendarClock class="size-4"/>
        {{ t('editor.unsaved.move_to_version') }}
      </button>
    </div>
  </div>
</template>