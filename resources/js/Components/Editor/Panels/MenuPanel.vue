<script setup lang="ts">
  import {computed, PropType, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Archive, ChevronDown, ChevronRight, Copy, Ellipsis, Eye, EyeOff, Plus, RotateCcw, Trash2} from 'lucide-vue-next'
  import type {EditorCategory} from '@/api'
  import {getEditorRestaurant, storeEditorMenu, updateEditorMenu} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import FieldLabel from '@/Components/Editor/Fields/FieldLabel.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {useContentLocale} from '@/composables/useContentLocale'
  import {useItemActions} from '@/composables/useItemActions'
  import {applyMenu, isListed, isShown, menusPreview, TextsDraft, textsOf, textsRequest} from '@/editor/menuDrafts'
  import type {Breadcrumb, Selection} from '@/editor/sections'
  import {translated} from '@/editor/translations'
  import {useEditorStore} from '@/stores/editor'

  /**
   * A menu: its name and description, and its categories. A new one is created on Save.
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
  const menu = computed(() => props.selection.id ? editor.findMenu(props.selection.id) : null)
  const isNew = computed(() => props.selection.id === null)

  // the id of the menu, which was created
  let created: number | null = null

  const {draft, dirty, saving, failed, discard, save, error} = usePanelDraft<TextsDraft>({
    saved: () => textsOf(menu.value, editor.locales),
    save: async (texts) => {
      if (menu.value) {
        await updateEditorMenu(menu.value.id, textsRequest(texts))
      } else {
        created = (await storeEditorMenu(restaurant.value.id, textsRequest(texts))).data.data.id
      }

      return (await getEditorRestaurant(restaurant.value.id)).data.data
    },
    preview: (texts, locale) => menusPreview(
      applyMenu(restaurant.value.menus, props.selection.id, texts, restaurant.value.id),
      locale,
      editor.defaultLocale,
    ),
  })

  const {locale, languages, placeholder, textError} = useContentLocale(
    () => [draft.value.title, draft.value.description]
  )

  const canSave = computed(() => !!draft.value.title[editor.defaultLocale]?.trim())

  const name = (item: { title: EditorCategory['title'] }) => translated(item.title, editor.defaultLocale)

  const title = computed(() => menu.value ? name(menu.value) : t('editor.menu.new'))

  const breadcrumbs = computed<Breadcrumb[]>(() => [
    {label: t('editor.panel.page_structure'), selection: null},
    {label: t('editor.sections.menus'), selection: {section: 'menus', id: null}},
  ])

  const categories = computed(() => (menu.value?.categories ?? []).filter(isListed))
  const archived = computed(() => (menu.value?.categories ?? []).filter((category) => category.archived))
  const showArchived = ref(false)

  async function onSave() {
    // a new menu: its panel, once it's created
    if (await save() && created) {
      editor.select({section: 'menu', id: created})
    }
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
      editor.select({section: 'menus', id: null})
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
              :dirty="dirty || isNew"
              :saving="saving"
              :failed="failed"
              :can-save="canSave"
              :save-label="isNew ? t('editor.menu.create') : null"
              @navigate="editor.select($event, !!$event)"
              @close="editor.close()"
              @discard="isNew ? editor.select({section: 'menus', id: null}) : discard()"
              @save="onSave">
    <template #actions v-if="menu">
      <DropdownMenu align="end">
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
                @click="editor.select({section: 'category', id: null, parent: menu.id})">
          <Plus class="size-[15px]"/>
          {{ t('editor.menus.add_category') }}
        </button>
      </div>

      <button type="button"
              class="min-h-11 flex items-center gap-2.5 px-1 border-b border-[#f0f0f1] text-start hover:bg-zinc-50 e-focus"
              v-for="category in categories" :key="category.id"
              @click="editor.select({section: 'category', id: category.id}, true)">
        <span class="flex-1 min-w-0 truncate"
              :class="{'text-zinc-400': category.is_hidden}">
          {{ name(category) }}
        </span>

        <span class="e-pill bg-zinc-100 text-zinc-600" v-if="category.is_hidden">
          <EyeOff class="size-3"/>
          {{ t('editor.menus.hidden') }}
        </span>

        <span class="text-[13px] text-zinc-500">
          {{ t('editor.structure.dishes_count', (category.dishes ?? []).filter(isListed).length) }}
        </span>
        <ChevronRight class="size-4 text-zinc-400"/>
      </button>

      <template v-if="archived.length">
        <button type="button"
                class="mt-3 e-section flex items-center gap-1.5 rounded e-focus"
                :aria-expanded="showArchived"
                @click="showArchived = !showArchived">
          <ChevronDown class="size-3.5" v-if="showArchived"/>
          <ChevronRight class="size-3.5" v-else/>
          {{ t('editor.menu.archived_categories', {count: archived.length}) }}
        </button>

        <template v-if="showArchived">
          <div class="mt-2 flex items-center gap-2.5 py-2 pl-3 pr-2 rounded-lg bg-zinc-50 border border-[#f0f0f1]"
               v-for="category in archived" :key="category.id">
            <span class="flex-1 min-w-0 font-semibold text-zinc-600 truncate">{{ name(category) }}</span>

            <button type="button"
                    class="e-btn e-btn-secondary h-8 px-2.5"
                    @click="actions.restore('category', category.id, name(category))">
              <RotateCcw class="size-[15px]"/>
              {{ t('editor.actions.restore') }}
            </button>

            <button type="button"
                    class="e-icon-btn text-red-700 hover:text-red-800"
                    :aria-label="t('editor.actions.delete_for_good', {name: name(category)})"
                    :title="t('editor.actions.delete_for_good', {name: name(category)})"
                    @click="actions.destroy('category', category.id, name(category), actions.categoryContents(category))">
              <Trash2 class="size-4"/>
            </button>
          </div>
        </template>
      </template>
    </section>
  </PanelShell>
</template>
