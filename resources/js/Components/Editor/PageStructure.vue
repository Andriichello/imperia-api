<script setup lang="ts">
  import {computed} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {BookOpen, CalendarClock, ChevronRight, Image, Info, MessageSquare} from 'lucide-vue-next'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import UnsavedPill from '@/Components/Editor/UnsavedPill.vue'
  import {useEditorStore} from '@/stores/editor'
  import {isSameSelection, Section, selectionOf} from '@/editor/sections'
  import {isListed} from '@/editor/menuDrafts'
  import {openState} from '@/editor/hours'
  import {presetOf} from '@/editor/brand'

  /**
   * The sections of the restaurant page: a click opens the panel of the section, and the
   * preview shows it. Menus, categories and dishes are managed in the Menus panel. The section
   * hovered in the preview is highlighted here, and the other way round.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const restaurant = computed(() => editor.restaurant!)

  const status = computed(() => openState(restaurant.value))

  const STATUS_DOTS = {
    open: 'bg-[#00a63e]',
    closed: 'bg-red-600',
    temporarily_closed: 'bg-amber-500',
  }

  /** "3 photos · 1 hidden" */
  function photosMeta(): string {
    const photos = restaurant.value.photos ?? []
    const hidden = photos.filter((photo) => photo.is_hidden).length

    return [t('editor.structure.photos_count', photos.length), hidden ? t('editor.structure.hidden_count', hidden) : null]
      .filter(Boolean)
      .join(' · ')
  }

  /** "3 menus · 42 dishes" (archived ones aside) */
  function menusMeta(): string {
    const dishes = editor.menus.reduce((count, menu) => count + (menu.categories ?? []).filter(isListed)
      .reduce((sum, category) => sum + (category.dishes ?? []).filter(isListed).length, 0), 0)

    return `${t('editor.structure.menus_count', editor.menus.length)} · ${t('editor.structure.dishes_count', dishes)}`
  }

  const sections = computed<{ section: Section, icon: typeof Image, meta: string, unsaved: boolean }[]>(() => [
    {
      section: 'photos',
      icon: Image,
      meta: photosMeta(),
      unsaved: editor.hasUnsaved(['photos']),
    },
    {
      section: 'details',
      icon: Info,
      meta: t('editor.structure.details_meta'),
      unsaved: editor.hasUnsaved(['details']),
    },
    {
      section: 'notes',
      icon: MessageSquare,
      meta: t('editor.structure.notes_count', restaurant.value.notes?.length ?? 0),
      unsaved: editor.hasUnsaved(['notes']),
    },
    {
      section: 'menus',
      icon: BookOpen,
      meta: menusMeta(),
      unsaved: editor.hasUnsaved(['menus', 'menu', 'category', 'dish']),
    },
    {
      section: 'hours',
      icon: CalendarClock,
      meta: t('editor.status.' + status.value),
      unsaved: editor.hasUnsaved(['hours']),
    },
  ])

  const brandName = computed(() => {
    const preset = presetOf(editor.brand)

    return preset ? t('editor.brand.presets.' + preset.key) : t('editor.structure.custom_colors')
  })

  /** The row of the section hovered in the preview. */
  function isHighlighted(section: Section): boolean {
    return !!editor.previewHover && isSameSelection(selectionOf(editor.previewHover), {section, id: null})
  }

  function open(section: Section) {
    editor.select({section, id: null}, true)
  }

  function hover(section: Section | null) {
    editor.treeHover = section ? {section, id: null} : null
  }
</script>

<template>
  <PanelShell :title="t('editor.structure.title')"
              :subtitle="t('editor.structure.help')"
              :closable="false"
              body-class="px-2.5 pt-4 pb-6 gap-[18px]">
    <!-- no breadcrumb, but its row stays: the title is where the other panels have it -->
    <template #breadcrumb>
      <div class="h-6" aria-hidden="true"/>
    </template>

    <section class="flex flex-col gap-0.5">
      <h3 class="e-section px-2.5 pb-1.5">{{ t('editor.structure.restaurant') }}</h3>

      <button type="button"
              class="h-10 w-full flex items-center gap-2.5 px-2.5 rounded-md text-start e-focus"
              :class="isHighlighted(item.section) ? 'bg-blue-50 text-blue-700' : 'hover:bg-zinc-50'"
              v-for="item in sections" :key="item.section"
              @click="open(item.section)"
              @mouseenter="hover(item.section)"
              @mouseleave="hover(null)"
              @focus="hover(item.section)"
              @blur="hover(null)">
        <component :is="item.icon"
                   class="size-4 shrink-0"
                   :class="{'text-zinc-500': !isHighlighted(item.section)}"/>
        <span class="font-medium whitespace-nowrap">{{ t('editor.sections.' + item.section) }}</span>

        <span class="ml-auto min-w-0 inline-flex items-center gap-1.5 text-[13px]"
              :class="{'text-zinc-500': !isHighlighted(item.section)}">
          <span class="size-[7px] shrink-0 rounded-full"
                :class="STATUS_DOTS[status]"
                aria-hidden="true"
                v-if="item.section === 'hours'"/>
          <span class="truncate">{{ item.meta }}</span>
        </span>

        <UnsavedPill v-if="item.unsaved"/>

        <ChevronRight class="size-4 shrink-0"
                      :class="{'text-zinc-400': !isHighlighted(item.section)}"/>
      </button>
    </section>

    <section class="flex flex-col gap-0.5">
      <h3 class="e-section px-2.5 pb-1.5">{{ t('editor.structure.appearance') }}</h3>

      <button type="button"
              class="h-10 w-full flex items-center gap-2.5 px-2.5 rounded-md text-start hover:bg-zinc-50 e-focus"
              @click="open('brand')">
        <span class="size-4 shrink-0 rounded-full shadow-[inset_0_0_0_1px_rgba(0,0,0,0.1)]"
              :style="{background: editor.brand.primary}"
              aria-hidden="true"/>
        <span class="font-medium whitespace-nowrap">{{ t('editor.sections.brand') }}</span>
        <span class="ml-auto min-w-0 truncate text-[13px] text-zinc-500">{{ brandName }}</span>
        <UnsavedPill v-if="editor.hasUnsaved(['brand'])"/>
        <ChevronRight class="size-4 shrink-0 text-zinc-400"/>
      </button>
    </section>
  </PanelShell>
</template>
