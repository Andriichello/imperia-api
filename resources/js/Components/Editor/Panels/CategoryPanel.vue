<script setup lang="ts">
  import {computed, PropType, ref} from 'vue'
  import {DateTime} from 'luxon'
  import {intlLocale} from '@/admin/format'
  import {useI18n} from 'vue-i18n'
  import {
    Archive,
    ArrowLeft,
    ChevronDown,
    ChevronRight,
    Copy,
    Ellipsis,
    Eye,
    EyeOff,
    FolderInput,
    Image,
    Plus,
  } from 'lucide-vue-next'
  import {VueDraggable} from 'vue-draggable-plus'
  import type {EditorDish, EditorMenu} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import GripHandle from '@/Components/Editor/Fields/GripHandle.vue'
  import {moveItem} from '@/editor/lists'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import FieldLabel from '@/Components/Editor/Fields/FieldLabel.vue'
  import InfoBox from '@/Components/Editor/Fields/InfoBox.vue'
  import UnsavedPill from '@/Components/Editor/UnsavedPill.vue'
  import NewDraftRow from '@/Components/Editor/NewDraftRow.vue'
  import ArchivedRow from '@/Components/Editor/ArchivedRow.vue'
  import ScheduledNotice from '@/Components/Editor/ScheduledNotice.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {useContentLocale} from '@/composables/useContentLocale'
  import {useItemActions} from '@/composables/useItemActions'
  import {CategoryDraft, isListed} from '@/editor/menuDrafts'
  import {Breadcrumb, isNewId, Selection} from '@/editor/sections'
  import {translated} from '@/editor/translations'
  import {priceFormatted, sizeWeightFormatted} from '@/helpers'
  import {useEditorStore} from '@/stores/editor'

  /**
   * A category: its name and description, and its dishes in their order. A new one is
   * created in its menu, when it's saved.
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
  const category = computed(() => isNew.value ? null : editor.findCategory(props.selection.id))
  const menu = computed(() => category.value
    ? editor.findMenu(category.value.menu_id)
    : editor.findMenu(props.selection.parent ?? null))

  const {draft, changed, error} = usePanelDraft<CategoryDraft>(props.selection)

  const {locale, languages, placeholder, textError} = useContentLocale(
    () => [draft.value.title, draft.value.description]
  )

  const name = (item: { title: EditorDish['title'] } | null) => item ? translated(item.title, editor.defaultLocale) : ''

  const title = computed(() => category.value ? name(category.value) : t('editor.category.new'))

  // the dishes in the draft's order
  const dishes = computed<EditorDish[]>({
    get: () => draft.value.dishes
      .map((id) => (category.value?.dishes ?? []).find((dish) => dish.id === id))
      .filter((dish): dish is EditorDish => !!dish),
    set: (value) => {
      draft.value.dishes = value.map((dish) => dish.id)
    },
  })

  const archived = computed(() => (category.value?.dishes ?? []).filter((dish) => dish.archived))
  const showArchived = ref(true)

  /** "Archived 2 Sep · 300 g · 150 ₴" */
  function archivedMeta(dish: EditorDish): string {
    const date = dish.archived_at
      ? DateTime.fromISO(dish.archived_at).setLocale(intlLocale(editor.locale)).toLocaleString({day: 'numeric', month: 'short'})
      : null

    return [date ? t('editor.menus.archived_on', {date}) : null, sizesMeta(dish)].filter(Boolean).join(' · ')
  }

  const breadcrumbs = computed<Breadcrumb[]>(() => [
    {label: t('editor.panel.page_structure'), selection: null},
    ...(menu.value ? [{label: name(menu.value), selection: {section: 'menu' as const, id: menu.value.id}}] : []),
  ])

  /** "300 g · 450 g · from 185 ₴", or "350 g · 140 ₴" (of the sizes guests see). */
  function sizesMeta(dish: EditorDish): string {
    const sizes = dish.sizes.filter((size) => !size.is_hidden).sort((a, b) => a.price - b.price)
    const weights = sizes.map((size) => sizeWeightFormatted(size)).filter(Boolean)
    const price = priceFormatted(sizes[0]?.price ?? null, restaurant.value.currency ?? 'uah')
    const parts = [...weights]

    if (price) {
      parts.push(sizes.length > 1 ? t('editor.category.from', {price}) : price)
    }

    return parts.join(' · ')
  }

  /** Its sizes, and when it has no photo. */
  function dishMeta(dish: EditorDish): string {
    const parts = [sizesMeta(dish)].filter(Boolean)

    if (!(dish.photos ?? []).some((photo) => !photo.is_hidden)) {
      parts.push(t('editor.category.no_photo'))
    }

    return parts.join(' · ')
  }

  /** Its cover: the first photo guests see. */
  function thumbnail(dish: EditorDish): string | null {
    const photo = (dish.photos ?? []).find((item) => !item.is_hidden)

    return photo ? (photo.variants?.find((variant) => variant.extension === 'webp')?.url ?? photo.url) : null
  }

  // the menus, which it can be moved to
  const otherMenus = computed<EditorMenu[]>(() => restaurant.value.menus
    .filter((other) => isListed(other) && other.id !== menu.value?.id))
  const moving = ref(false)

  async function move(target: EditorMenu) {
    if (category.value) {
      await actions.moveCategory(category.value.id, target.id, name(category.value), name(target))
    }
  }

  async function archive() {
    if (category.value && await actions.archive('category', category.value.id, name(category.value))) {
      editor.select(menu.value ? {section: 'menu', id: menu.value.id} : null, false, 'replace')
    }
  }

  async function duplicate() {
    if (!category.value) {
      return
    }

    const id = await actions.duplicate('category', category.value.id, name(category.value))

    if (id) {
      editor.select({section: 'category', id})
    }
  }
</script>

<template>
  <PanelShell :breadcrumbs="breadcrumbs"
              :title="title"
              :subtitle="menu ? t('editor.subtitles.category', {menu: name(menu)}) : null"
              :languages="languages"
              v-model:locale="locale"
              @navigate="editor.select($event, !!$event)"
              @close="editor.close()">
    <template #notice v-if="category">
      <ScheduledNotice :selection="selection"/>
    </template>

    <template #actions v-if="category">
      <DropdownMenu align="end">
        <template #trigger="{open, toggle}">
          <button type="button"
                  class="e-icon-btn"
                  aria-haspopup="menu"
                  :aria-expanded="open"
                  :aria-label="t('editor.panel.more_actions', {name: title})"
                  @click="moving = false; toggle()">
            <Ellipsis class="size-[18px]"/>
          </button>
        </template>

        <template v-if="moving">
          <button type="button" class="e-dropdown-item text-zinc-500" role="menuitem" @click.stop="moving = false">
            <ArrowLeft class="size-4"/>
            {{ t('editor.category.move_to') }}
          </button>

          <button type="button" class="e-dropdown-item" role="menuitem"
                  v-for="target in otherMenus" :key="target.id"
                  @click="move(target)">
            <span class="truncate">{{ name(target) }}</span>
          </button>
        </template>

        <template v-else>
          <button type="button" class="e-dropdown-item" role="menuitem" @click="draft.is_hidden = !draft.is_hidden">
            <template v-if="draft.is_hidden">
              <Eye class="size-4 text-zinc-500"/>
              {{ t('editor.actions.show') }}
            </template>
            <template v-else>
              <EyeOff class="size-4 text-zinc-500"/>
              {{ t('editor.actions.hide') }}
            </template>
          </button>

          <button type="button" class="e-dropdown-item" role="menuitem"
                  v-if="otherMenus.length"
                  @click.stop="moving = true">
            <FolderInput class="size-4 text-zinc-500"/>
            <span class="flex-1">{{ t('editor.category.move_to') }}</span>
            <ChevronRight class="size-4 text-zinc-400"/>
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
        </template>
      </DropdownMenu>
    </template>

    <div class="flex items-center gap-3 p-3 rounded-lg bg-zinc-100 text-[13px]/[18px] text-zinc-700"
         v-if="draft.is_hidden">
      <EyeOff class="size-4 shrink-0 text-zinc-500"/>
      <p class="flex-1">{{ t('editor.category.hidden') }}</p>
      <button type="button"
              class="e-btn e-btn-secondary h-8 px-2.5"
              @click="draft.is_hidden = false">
        {{ t('editor.actions.show_short') }}
      </button>
    </div>

    <div class="flex flex-col gap-1.5">
      <FieldLabel target="category-title" :label="t('editor.category.name')" :locale="locale"/>

      <input id="category-title"
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
      <FieldLabel target="category-description" :label="t('editor.category.description')" :locale="locale"/>

      <textarea id="category-description"
                class="e-input"
                rows="2"
                maxlength="1000"
                :placeholder="placeholder(draft.description)"
                :aria-invalid="!!textError(error, 'description')"
                :class="{'e-changed': changed((values) => values.description[locale])}"
                v-model="draft.description[locale]"/>

      <p class="e-error" v-if="textError(error, 'description')">{{ textError(error, 'description') }}</p>
      <p class="e-help" v-else>{{ t('editor.category.description_help') }}</p>
    </div>

    <section class="flex flex-col"
             v-if="category">
      <div class="flex items-center justify-between mb-1">
        <h3 class="e-section">{{ t('editor.category.dishes', {count: dishes.length}) }}</h3>

        <button type="button"
                class="e-btn e-btn-secondary h-8 px-2.5"
                @click="editor.add('dish', category.id)">
          <Plus class="size-[15px]"/>
          {{ t('editor.category.add_dish') }}
        </button>
      </div>

      <p class="py-2 text-[13px] text-zinc-500"
         v-if="!dishes.length && !editor.newItems('dish', category.id).length">
        {{ t('editor.category.no_dishes') }}
      </p>

      <VueDraggable class="flex flex-col"
                    v-model="dishes"
                    handle=".e-grip"
                    ghost-class="e-drag-ghost"
                    :animation="150">
        <div class="flex items-center gap-2.5 min-h-[60px] py-2 pr-1 border-b border-[#f0f0f1]"
             v-for="(dish, index) in dishes" :key="dish.id">
          <GripHandle :name="name(dish)"
                      :index="index"
                      :count="dishes.length"
                      @move="(from, to) => dishes = moveItem(dishes, from, to)"/>

          <button type="button"
                  class="flex-1 min-w-0 flex items-center gap-2.5 text-start rounded e-focus"
                  @click="editor.select({section: 'dish', id: dish.id}, true)">
            <img class="size-11 shrink-0 rounded-md object-cover shadow-[inset_0_0_0_1px_rgba(0,0,0,0.06)]"
                 :src="thumbnail(dish)!"
                 alt=""
                 draggable="false"
                 v-if="thumbnail(dish)"/>

            <span class="size-11 shrink-0 flex items-center justify-center rounded-md border border-dashed border-zinc-300 text-zinc-400"
                  aria-hidden="true"
                  v-else>
              <Image class="size-4"/>
            </span>

            <span class="flex-1 min-w-0 flex flex-col gap-0.5">
              <span class="flex items-center gap-1.5 font-semibold"
                    :class="dish.is_hidden ? 'text-zinc-400' : 'text-zinc-900'">
                <span class="truncate">{{ name(dish) }}</span>
                <span class="e-pill e-pill-sm bg-zinc-100 text-zinc-700 max-w-32 truncate"
                      v-if="translated(dish.badge, editor.defaultLocale)">
                  {{ translated(dish.badge, editor.defaultLocale) }}
                </span>
              </span>
              <span class="text-xs text-zinc-500">{{ dishMeta(dish) }}</span>
            </span>

            <UnsavedPill v-if="editor.isUnsaved({section: 'dish', id: dish.id})"/>

            <span class="e-pill bg-zinc-100 text-zinc-600" v-if="dish.is_hidden">
              <EyeOff class="size-3"/>
              {{ t('editor.menus.hidden') }}
            </span>

            <ChevronRight class="size-4 shrink-0 mr-1.5 text-zinc-400"/>
          </button>
        </div>
      </VueDraggable>

      <div class="py-1 border-b border-[#f0f0f1]"
           v-for="entry in editor.newItems('dish', category.id)" :key="entry.key">
        <NewDraftRow :entry="entry"/>
      </div>

      <template v-if="archived.length">
        <button type="button"
                class="mt-3 e-section flex items-center gap-1.5 rounded e-focus"
                :aria-expanded="showArchived"
                @click="showArchived = !showArchived">
          <ChevronDown class="size-3.5" v-if="showArchived"/>
          <ChevronRight class="size-3.5" v-else/>
          {{ t('editor.category.archived_dishes', {count: archived.length}) }}
        </button>

        <div class="mt-1 flex flex-col"
             v-if="showArchived">
          <ArchivedRow :name="name(dish)"
                       :badge="translated(dish.badge, editor.defaultLocale) || null"
                       :meta="archivedMeta(dish)"
                       :thumbnail="thumbnail(dish)"
                       v-for="dish in archived" :key="dish.id"
                       @restore="actions.restore('dish', dish.id, name(dish))"
                       @delete="actions.destroy('dish', dish.id, name(dish))"/>
        </div>
      </template>
    </section>

    <InfoBox v-if="category">{{ t('editor.category.info') }}</InfoBox>
  </PanelShell>
</template>
