<script setup lang="ts">
  import {computed, PropType, ref} from 'vue'
  import {DateTime} from 'luxon'
  import {intlLocale} from '@/admin/format'
  import {useI18n} from 'vue-i18n'
  import {Archive, ChevronDown, ChevronRight, Copy, Ellipsis, Eye, EyeOff, Plus} from 'lucide-vue-next'
  import type {EditorCategory} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import FieldLabel from '@/Components/Editor/Fields/FieldLabel.vue'
  import UnsavedPill from '@/Components/Editor/UnsavedPill.vue'
  import NewDraftRow from '@/Components/Editor/NewDraftRow.vue'
  import ArchivedRow from '@/Components/Editor/ArchivedRow.vue'
  import ScheduledNotice from '@/Components/Editor/ScheduledNotice.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {useContentLocale} from '@/composables/useContentLocale'
  import {useItemActions} from '@/composables/useItemActions'
  import {isListed, isShown, TextsDraft} from '@/editor/menuDrafts'
  import {Breadcrumb, isNewId, Selection} from '@/editor/sections'
  import {translated} from '@/editor/translations'
  import {useEditorStore} from '@/stores/editor'

  /**
   * A menu: its name and description, and its categories. A new one is created, when it's saved.
   */
  const props = defineProps({
    selection: {
      type: Object as PropType<Selection>,
      required: true,
    },
  })

  const editor = useEditorStore()
  const {t} = useI18n()
  const actions = useItemActions()

  const restaurant = computed(() => editor.restaurant!)
  const isNew = computed(() => isNewId(props.selection.id))
  const menu = computed(() => isNew.value ? null : editor.findMenu(props.selection.id))

  const {draft, changed, error} = usePanelDraft<TextsDraft>(props.selection)

  const {locale, languages, placeholder, textError} = useContentLocale(
    () => [draft.value.title, draft.value.description]
  )

  const name = (item: { title: EditorCategory['title'] }) => translated(item.title, editor.defaultLocale)

  const title = computed(() => menu.value ? name(menu.value) : t('editor.menu.new'))

  const breadcrumbs = computed<Breadcrumb[]>(() => [
    {label: t('editor.panel.page_structure'), selection: null},
    {label: t('editor.sections.menus'), selection: {section: 'menus', id: null}},
  ])

  const categories = computed(() => (menu.value?.categories ?? []).filter(isListed))
  const archived = computed(() => (menu.value?.categories ?? []).filter((category) => category.archived))
  const showArchived = ref(true)

  /** "Archived 2 Sep · 4 dishes" */
  function archivedMeta(category: EditorCategory): string {
    const date = category.archived_at
      ? DateTime.fromISO(category.archived_at).setLocale(intlLocale(editor.locale)).toLocaleString({day: 'numeric', month: 'short'})
      : null

    return [
      date ? t('editor.menus.archived_on', {date}) : null,
      t('editor.structure.dishes_count', (category.dishes ?? []).filter(isListed).length),
    ].filter(Boolean).join(' · ')
  }

  /** Guests see only this menu: hiding or archiving it leaves the menu page empty. */
  async function confirmLastVisible(): Promise<boolean> {
    const others = restaurant.value.menus.filter((other) => other.id !== menu.value?.id && isShown(other))

    if (draft.value.is_hidden || menu.value?.archived || others.length) {
      return true
    }

    return editor.confirm({
      title: t('editor.menus.last_visible_title'),
      message: t('editor.menus.last_visible_message'),
      confirm: t('editor.menus.continue'),
    })
  }

  async function toggleHidden() {
    if (await confirmLastVisible()) {
      draft.value.is_hidden = !draft.value.is_hidden
    }
  }

  async function archive() {
    if (menu.value && await confirmLastVisible() && await actions.archive('menu', menu.value.id, name(menu.value))) {
      editor.select({section: 'menus', id: null}, false, 'replace')
    }
  }

  async function duplicate() {
    if (!menu.value) {
      return
    }

    const id = await actions.duplicate('menu', menu.value.id, name(menu.value))

    if (id) {
      editor.select({section: 'menu', id})
    }
  }
</script>

<template>
  <PanelShell :breadcrumbs="breadcrumbs"
              :title="title"
              :subtitle="t('editor.subtitles.menu')"
              :languages="languages"
              v-model:locale="locale"
              @navigate="editor.select($event, !!$event)"
              @close="editor.close()">
    <template #notice>
      <ScheduledNotice :selection="selection" v-if="menu"/>
    </template>

    <template #actions>
      <DropdownMenu align="end" v-if="menu">
        <template #trigger="{open, toggle}">
          <button type="button"
                  class="e-icon-btn"
                  aria-haspopup="menu"
                  :aria-expanded="open"
                  :aria-label="t('editor.panel.more_actions', {name: title})"
                  @click="toggle">
            <Ellipsis class="size-[18px]"/>
          </button>
        </template>

        <button type="button" class="e-dropdown-item" role="menuitem" @click="toggleHidden">
          <template v-if="draft.is_hidden">
            <Eye class="size-4 text-zinc-500"/>
            {{ t('editor.actions.show') }}
          </template>
          <template v-else>
            <EyeOff class="size-4 text-zinc-500"/>
            {{ t('editor.actions.hide') }}
          </template>
        </button>

        <button type="button" class="e-dropdown-item" role="menuitem" @click="duplicate">
          <Copy class="size-4 text-zinc-500"/>
          {{ t('editor.actions.duplicate') }}
        </button>

        <div class="h-px my-1 bg-[#f0f0f1]"/>

        <button type="button" class="e-dropdown-item" role="menuitem" @click="archive">
          <Archive class="size-4 text-zinc-500"/>
          {{ t('editor.actions.archive') }}
        </button>
      </DropdownMenu>
    </template>

    <div class="flex items-center gap-3 p-3 rounded-lg bg-zinc-100 text-[13px]/[18px] text-zinc-700"
         v-if="draft.is_hidden">
      <EyeOff class="size-4 shrink-0 text-zinc-500"/>
      <p class="flex-1">{{ t('editor.menu.hidden') }}</p>
      <button type="button"
              class="e-btn e-btn-secondary h-8 px-2.5"
              @click="draft.is_hidden = false">
        {{ t('editor.actions.show_short') }}
      </button>
    </div>

    <div class="flex flex-col gap-1.5">
      <FieldLabel target="menu-title" :label="t('editor.menu.name')" :locale="locale"/>

      <input id="menu-title"
             class="e-input"
             type="text"
             maxlength="255"
             :placeholder="placeholder(draft.title)"
             :aria-invalid="!!textError(error, 'title')"
             :class="{'e-changed': changed((values) => values.title[locale])}"
             v-model="draft.title[locale]"/>

      <p class="e-error" v-if="textError(error, 'title')">{{ textError(error, 'title') }}</p>
    </div>

    <div class="flex flex-col gap-1.5">
      <FieldLabel target="menu-description" :label="t('editor.menu.description')" :locale="locale"/>

      <textarea id="menu-description"
                class="e-input"
                rows="2"
                maxlength="1000"
                :placeholder="placeholder(draft.description)"
                :aria-invalid="!!textError(error, 'description')"
                :class="{'e-changed': changed((values) => values.description[locale])}"
                v-model="draft.description[locale]"/>

      <p class="e-error" v-if="textError(error, 'description')">{{ textError(error, 'description') }}</p>
      <p class="e-help" v-else>{{ t('editor.menu.description_help') }}</p>
    </div>

    <section class="flex flex-col"
             v-if="menu">
      <div class="flex items-center justify-between mb-1">
        <h3 class="e-section">{{ t('editor.menu.categories', {count: categories.length}) }}</h3>

        <button type="button"
                class="e-btn e-btn-secondary h-8 px-2.5"
                @click="editor.add('category', menu.id)">
          <Plus class="size-[15px]"/>
          {{ t('editor.menus.add_category') }}
        </button>
      </div>

      <p class="py-2 text-[13px] text-zinc-500"
         v-if="!categories.length && !editor.newItems('category', menu.id).length">
        {{ t('editor.menu.no_categories') }}
      </p>

      <button type="button"
              class="min-h-11 flex items-center gap-2.5 px-1 border-b border-[#f0f0f1] text-start hover:bg-zinc-50 e-focus"
              v-for="category in categories" :key="category.id"
              @click="editor.select({section: 'category', id: category.id}, true)">
        <span class="flex-1 min-w-0 truncate"
              :class="{'text-zinc-400': category.is_hidden}">
          {{ name(category) }}
        </span>

        <UnsavedPill v-if="editor.isUnsaved({section: 'category', id: category.id})"/>

        <span class="e-pill bg-zinc-100 text-zinc-600" v-if="category.is_hidden">
          <EyeOff class="size-3"/>
          {{ t('editor.menus.hidden') }}
        </span>

        <span class="text-[13px] text-zinc-500">
          {{ t('editor.structure.dishes_count', (category.dishes ?? []).filter(isListed).length) }}
        </span>
        <ChevronRight class="size-4 text-zinc-400"/>
      </button>

      <div class="py-0.5 border-b border-[#f0f0f1]"
           v-for="entry in editor.newItems('category', menu.id)" :key="entry.key">
        <NewDraftRow :entry="entry"/>
      </div>

      <template v-if="archived.length">
        <button type="button"
                class="mt-3 e-section flex items-center gap-1.5 rounded e-focus"
                :aria-expanded="showArchived"
                @click="showArchived = !showArchived">
          <ChevronDown class="size-3.5" v-if="showArchived"/>
          <ChevronRight class="size-3.5" v-else/>
          {{ t('editor.menu.archived_categories', {count: archived.length}) }}
        </button>

        <div class="mt-1 flex flex-col"
             v-if="showArchived">
          <ArchivedRow :name="name(category)"
                       :meta="archivedMeta(category)"
                       v-for="category in archived" :key="category.id"
                       @restore="actions.restore('category', category.id, name(category))"
                       @delete="actions.destroy('category', category.id, name(category), actions.categoryContents(category))"/>
        </div>
      </template>
    </section>
  </PanelShell>
</template>
