<script setup lang="ts">
  import {computed, PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Info} from 'lucide-vue-next'
  import type {EditorCategory, EditorDish, EditorMenu, EditorTranslations} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import {useEditorStore} from '@/stores/editor'
  import {useContentLocale} from '@/composables/useContentLocale'
  import type {Breadcrumb, LanguageTab, Section, Selection} from '@/editor/sections'
  import {translated} from '@/editor/translations'

  /**
   * The panel of a part of the page: its frame, with what it's about. The fields of each
   * part come with the next steps of the editor.
   */
  const props = defineProps({
    selection: {
      type: Object as PropType<Selection>,
      required: true,
    },
  })

  const editor = useEditorStore()
  const {t} = useI18n()

  /** Panels with texts in each language. */
  const TRANSLATED: Section[] = ['details', 'notes', 'menu', 'category', 'dish']

  const section = computed(() => props.selection.section)

  const name = (value: EditorTranslations) => translated(value, editor.defaultLocale)

  const dish = computed<EditorDish | null>(
    () => section.value === 'dish' ? editor.findDish(props.selection.id) : null
  )

  const category = computed<EditorCategory | null>(() => {
    if (section.value === 'category') {
      return editor.findCategory(props.selection.id)
    }

    return dish.value ? editor.findCategory(dish.value.category_id) : null
  })

  const menu = computed<EditorMenu | null>(() => {
    if (section.value === 'menu') {
      return editor.findMenu(props.selection.id)
    }

    return category.value ? editor.findMenu(category.value.menu_id) : null
  })

  // the menu, category or dish of the panel
  const item = computed(() => ({menu: menu.value, category: category.value, dish: dish.value})[section.value as string] ?? null)

  const isItem = computed(() => ['menu', 'category', 'dish'].includes(section.value))

  const title = computed(() => isItem.value && item.value
    ? name(item.value.title)
    : t('editor.sections.' + section.value))

  const subtitle = computed(() => t('editor.subtitles.' + section.value, {
    menu: menu.value ? name(menu.value.title) : '',
    category: category.value ? name(category.value.title) : '',
  }))

  const breadcrumbs = computed<Breadcrumb[]>(() => {
    const crumbs: Breadcrumb[] = [{label: t('editor.panel.page_structure'), selection: null}]

    if (section.value === 'menu') {
      crumbs.push({label: t('editor.sections.menus'), selection: {section: 'menus', id: null}})
    }

    if ((section.value === 'category' || section.value === 'dish') && menu.value) {
      crumbs.push({label: name(menu.value.title), selection: {section: 'menu', id: menu.value.id}})
    }

    if (section.value === 'dish' && category.value) {
      crumbs.push({label: name(category.value.title), selection: {section: 'category', id: category.value.id}})
    }

    return crumbs
  })

  // texts in each language
  const {locale, languages: tabs} = useContentLocale()

  const languages = computed<LanguageTab[] | null>(() => TRANSLATED.includes(section.value) ? tabs.value : null)

  function navigate(selection: Selection | null) {
    editor.select(selection, !!selection && editor.isShown(selection))
  }
</script>

<template>
  <PanelShell :breadcrumbs="breadcrumbs"
              :title="title"
              :subtitle="isItem && !item ? null : subtitle"
              :languages="languages"
              v-model:locale="locale"
              @navigate="navigate"
              @close="editor.close()">
    <div class="flex gap-2.5 p-3 rounded-lg bg-zinc-100 text-[13px]/[18px] text-zinc-700">
      <Info class="size-4 shrink-0 mt-px text-zinc-500"/>

      <p>{{ isItem && !item ? t('editor.panel.not_found') : t('editor.panel.next_step') }}</p>
    </div>
  </PanelShell>
</template>
