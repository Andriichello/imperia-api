<script setup lang="ts">
  import {computed, nextTick, PropType, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {
    Archive,
    ArrowLeft,
    ArrowUpNarrowWide,
    Check,
    ChevronRight,
    Copy,
    Ellipsis,
    Eye,
    EyeOff,
    FolderInput,
    MousePointer2,
    Plus,
    Trash2,
  } from 'lucide-vue-next'
  import type {EditorCategory, EditorDish} from '@/api'
  import {EditorSizeWeightUnit} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import FieldLabel from '@/Components/Editor/Fields/FieldLabel.vue'
  import ToggleSwitch from '@/Components/Editor/Fields/ToggleSwitch.vue'
  import PhotoGallery from '@/Components/Editor/PhotoGallery.vue'
  import PanelField from '@/Components/Editor/Fields/PanelField.vue'
  import SelectedInPreview from '@/Components/Editor/Fields/SelectedInPreview.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {useContentLocale} from '@/composables/useContentLocale'
  import {useItemActions} from '@/composables/useItemActions'
  import {DishDraft, isListed, SizeDraft, sizeOf, sortSizes} from '@/editor/menuDrafts'
  import ScheduledNotice from '@/Components/Editor/ScheduledNotice.vue'
  import {Breadcrumb, isNewId, Selection} from '@/editor/sections'
  import {translated} from '@/editor/translations'
  import {ALLERGENS, getAllergenLabel, HOTNESS, TAG_GROUPS, tagsOf, withTag} from '@/flags'
  import {priceFormatted} from '@/helpers'
  import {useEditorStore} from '@/stores/editor'

  /**
   * A dish: whether guests see it, its photo, texts, sizes with prices, preparation time and
   * calories, tags and allergens. A new one is created in its category, when it's saved.
   * On the dish's page in the preview, a click on a part of it shows that part here.
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

  const MAX_DESCRIPTION = 300
  const MAX_BADGE = 25
  const MAX_SIZES = 10
  // the most photos of a dish, hidden ones included
  const MAX_PHOTOS = 3
  const UNITS = Object.values(EditorSizeWeightUnit)
  const QUICK_PICKS = ['new', 'bestseller', 'seasonal']

  const restaurant = computed(() => editor.restaurant!)
  const isNew = computed(() => isNewId(props.selection.id))
  const dish = computed(() => isNew.value ? null : editor.findDish(props.selection.id))
  const category = computed<EditorCategory | null>(() => editor.findCategory(dish.value?.category_id ?? props.selection.parent ?? null))
  const menu = computed(() => category.value ? editor.findMenu(category.value.menu_id) : null)

  const {draft, saved, changed, change, error} = usePanelDraft<DishDraft>(props.selection)

  // the preview shows the dish's page: a click on a part of it shows the part here
  const onItsPage = computed(() => !!dish.value && editor.page.page === 'dish' && editor.page.dishId === dish.value.id)

  const {locale, languages, placeholder, textError} = useContentLocale(
    () => [draft.value.title, draft.value.description, draft.value.badge]
  )

  const name = (item: { title: EditorDish['title'] } | null) => item ? translated(item.title, editor.defaultLocale) : ''

  const title = computed(() => dish.value ? name(dish.value) : t('editor.dish.new'))

  const breadcrumbs = computed<Breadcrumb[]>(() => [
    {label: t('editor.panel.page_structure'), selection: null},
    ...(menu.value ? [{label: name(menu.value), selection: {section: 'menu' as const, id: menu.value.id}}] : []),
    ...(category.value
      ? [{label: name(category.value), selection: {section: 'category' as const, id: category.value.id}}]
      : []),
  ])

  const currency = computed(() => t(`currency_symbol.${(restaurant.value.currency ?? 'uah').toLowerCase()}`))

  // Sizes

  const isNumber = (value: string, optional: boolean) => value.trim() === ''
    ? optional
    : /^\d+([.,]\d+)?$/.test(value.trim())

  const isWhole = (value: string) => value.trim() === '' || /^\d+$/.test(value.trim())

  // saved sizes by their ids: changed values are marked
  const savedSizes = computed(() => new Map(saved.value.sizes.filter((size) => size.id).map((size) => [size.id, size])))

  const shownSizes = computed(() => draft.value.sizes.filter((size) => !size.is_hidden).length)

  /** Whether the size's value differs from the saved one (a new size isn't marked). */
  function sizeChanged(size: SizeDraft, field: keyof SizeDraft): boolean {
    const before = size.id ? savedSizes.value.get(size.id) : null

    return !!before && before[field] !== size[field]
  }

  /** "Was 185 ₴" under a changed price. */
  function wasPrice(size: SizeDraft): string | null {
    const before = size.id ? savedSizes.value.get(size.id) : null

    return before && before.price !== size.price
      ? t('editor.dish.was', {value: priceFormatted(Number(before.price), (restaurant.value.currency ?? 'uah').toLowerCase())})
      : null
  }

  /** "300 g · 185 ₴" in the size's header. */
  function sizeTitle(size: SizeDraft, index: number): string {
    const weight = size.weight.trim() ? `${size.weight.trim()} ${t('weight_unit.' + size.weight_unit)}` : ''
    const price = isNumber(size.price, false)
      ? priceFormatted(Number(size.price.replace(',', '.')), (restaurant.value.currency ?? 'uah').toLowerCase())
      : ''

    return [weight, price].filter(Boolean).join(' · ') || t('editor.dish.size', {number: index + 1})
  }

  function sizeError(size: SizeDraft): string | null {
    if (!isNumber(size.price, false)) {
      return t('editor.dish.price_needed')
    }

    if (!isNumber(size.weight, true)) {
      return t('editor.dish.size_number')
    }

    if (!isWhole(size.preparation_time) || !isWhole(size.calories)) {
      return t('editor.dish.whole_number')
    }

    return ['price', 'weight', 'weight_unit', 'calories', 'preparation_time', 'is_hidden']
      .map((key) => error(`sizes.${size.key}.${key}`))
      .find(Boolean) ?? null
  }

  function addSize() {
    const last = draft.value.sizes[draft.value.sizes.length - 1]

    draft.value.sizes.push(sizeOf({
      weight_unit: (last?.weight_unit ?? 'g') as EditorDish['sizes'][number]['weight_unit'],
    }))

    // its weight is typed first
    nextTick(() => document.getElementById(`size-${draft.value.sizes[draft.value.sizes.length - 1].key}-weight`)?.focus())
  }

  /** Sizes go from the cheapest one: a price sorts them again, once its field is left (not while it's typed). */
  function sortByPrice() {
    draft.value.sizes = sortSizes(draft.value.sizes)
  }

  /** Hide the size from guests, or show it again: a dish keeps a size they see. */
  function toggleSize(size: SizeDraft) {
    if (size.is_hidden || shownSizes.value > 1) {
      size.is_hidden = !size.is_hidden
    }
  }

  /** Delete the size: when the dish is saved, it's archived (a dish keeps a size guests see). */
  function removeSize(size: SizeDraft) {
    if (draft.value.sizes.length > 1 && (size.is_hidden || shownSizes.value > 1)) {
      draft.value.sizes = draft.value.sizes.filter((item) => item.key !== size.key)
    }
  }

  // Flags

  function hasFlag(flag: string): boolean {
    return draft.value.flags.includes(flag)
  }

  function toggleFlag(flag: string) {
    draft.value.flags = hasFlag(flag)
      ? draft.value.flags.filter((other) => other !== flag)
      : [...draft.value.flags, flag]
  }

  /** Picks a tag or unpicks it: one level of hotness at most, and either low or high of the same thing. */
  function toggleTag(key: string) {
    draft.value.flags = hasFlag(key)
      ? draft.value.flags.filter((other) => other !== key)
      : withTag(draft.value.flags, key)
  }

  const spicy = computed(() => draft.value.flags.some((flag) => HOTNESS.includes(flag)))

  function setNotSpicy() {
    draft.value.flags = draft.value.flags.filter((flag) => !HOTNESS.includes(flag))
  }

  const allergens = computed(() => draft.value.flags.filter((flag) => ALLERGENS.includes(flag)))

  // Actions

  // categories of all the menus, which it can be moved to
  const targets = computed(() => restaurant.value.menus.filter(isListed).flatMap((other) => (other.categories ?? [])
    .filter((item) => isListed(item) && item.id !== category.value?.id)
    .map((item) => ({category: item, label: `${name(other)} › ${name(item)}`}))))
  const moving = ref(false)

  async function move(target: EditorCategory) {
    if (dish.value) {
      await actions.moveDish(dish.value.id, target.id, name(dish.value), name(target))
    }
  }

  async function archive() {
    if (dish.value && await actions.archive('dish', dish.value.id, name(dish.value))) {
      editor.select(category.value ? {section: 'category', id: category.value.id} : null, false, 'replace')
    }
  }

  async function duplicate() {
    if (!dish.value) {
      return
    }

    const id = await actions.duplicate('dish', dish.value.id, name(dish.value))

    if (id) {
      editor.select({section: 'dish', id})
    }
  }
</script>

<template>
  <PanelShell :breadcrumbs="breadcrumbs"
              :title="title"
              :subtitle="category ? t('editor.subtitles.dish', {category: name(category)}) : null"
              :languages="languages"
              v-model:locale="locale"
              @navigate="editor.select($event, !!$event)"
              @close="editor.close()">
    <template #notice>
      <ScheduledNotice :selection="selection"/>

      <div class="shrink-0 flex gap-2.5 mx-5 mt-3 px-3 py-2.5 rounded-lg bg-blue-50 border border-blue-200 text-[13px]/[18px] text-blue-900"
           role="status"
           v-if="onItsPage">
        <MousePointer2 class="size-4 shrink-0 mt-px"/>
        <p class="flex-1">
          {{ t('editor.dish.on_its_page') }}
          <button type="button"
                  class="font-semibold underline rounded e-focus"
                  @click="editor.openPage({page: 'menu', menuId: editor.page.menuId})">
            {{ t('editor.dish.back_to_list') }}
          </button>
        </p>
      </div>
    </template>

    <template #actions v-if="dish">
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
            {{ t('editor.dish.move_to') }}
          </button>

          <button type="button" class="e-dropdown-item" role="menuitem"
                  v-for="target in targets" :key="target.category.id"
                  @click="move(target.category)">
            <span class="truncate">{{ target.label }}</span>
          </button>
        </template>

        <template v-else>
          <button type="button" class="e-dropdown-item" role="menuitem" @click="duplicate">
            <Copy class="size-4 text-zinc-500"/>
            {{ t('editor.actions.duplicate') }}
          </button>

          <button type="button" class="e-dropdown-item" role="menuitem"
                  v-if="targets.length"
                  @click.stop="moving = true">
            <FolderInput class="size-4 text-zinc-500"/>
            <span class="flex-1">{{ t('editor.dish.move_to') }}</span>
            <ChevronRight class="size-4 text-zinc-400"/>
          </button>

          <div class="h-px my-1 bg-[#f0f0f1]"/>

          <button type="button" class="e-dropdown-item" role="menuitem" @click="archive">
            <Archive class="size-4 text-zinc-500"/>
            {{ t('editor.actions.archive') }}
          </button>
        </template>
      </DropdownMenu>
    </template>

    <div class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200">
      <div class="flex-1 flex flex-col gap-0.5">
        <p class="font-semibold">{{ t('editor.dish.shown') }}</p>
        <p class="e-help">{{ t('editor.dish.shown_help') }}</p>
      </div>

      <ToggleSwitch :model-value="!draft.is_hidden"
                    :label="t('editor.dish.shown')"
                    @update:model-value="draft.is_hidden = !$event"/>
    </div>

    <PanelField field="photos" v-slot="{selected}">
      <PhotoGallery v-model="draft.photos"
                    compact
                    :max="MAX_PHOTOS"
                    :uploaded="(photo) => change((values) => ({...values, photos: [...values.photos, photo]}))"
                    :error="error('media')"
                    :help="t('editor.dish.photos_help')">
        <template #label>
          <SelectedInPreview v-if="selected"/>
        </template>
      </PhotoGallery>
    </PanelField>

    <PanelField class="flex flex-col gap-5" field="text" v-slot="{selected}">
      <div class="flex flex-col gap-1.5">
        <FieldLabel target="dish-title" :label="t('editor.dish.name')" :locale="locale">
          <SelectedInPreview v-if="selected"/>
        </FieldLabel>

        <input id="dish-title"
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
        <FieldLabel target="dish-description" :label="t('editor.dish.description')" :locale="locale">
          <span class="e-help ml-auto tabular-nums">{{ draft.description[locale].length }} / {{ MAX_DESCRIPTION }}</span>
        </FieldLabel>

        <textarea id="dish-description"
                  class="e-input"
                  rows="3"
                  :maxlength="MAX_DESCRIPTION"
                  :placeholder="placeholder(draft.description)"
                  :aria-invalid="!!textError(error, 'description')"
                  :class="{'e-changed': changed((values) => values.description[locale])}"
                  v-model="draft.description[locale]"/>

        <p class="e-error" v-if="textError(error, 'description')">{{ textError(error, 'description') }}</p>
      </div>
    </PanelField>

    <PanelField field="badge" v-slot="{selected}">
      <div class="flex flex-col gap-1.5">
        <FieldLabel target="dish-badge" :label="t('editor.dish.badge')" :locale="locale">
          <SelectedInPreview v-if="selected"/>
        </FieldLabel>

        <input id="dish-badge"
               class="e-input"
               type="text"
               :maxlength="MAX_BADGE"
               :placeholder="placeholder(draft.badge)"
               :aria-invalid="!!textError(error, 'badge')"
               :class="{'e-changed': changed((values) => values.badge[locale])}"
               v-model="draft.badge[locale]"/>

        <div class="flex flex-wrap gap-1.5">
          <span class="e-help self-center">{{ t('editor.dish.quick_pick') }}</span>

          <button type="button"
                  class="e-pill bg-zinc-100 text-zinc-700 hover:bg-zinc-200 e-focus"
                  :lang="locale"
                  v-for="pick in QUICK_PICKS" :key="pick"
                  @click="draft.badge[locale] = t(`editor.dish.picks.${pick}`, {}, {locale})">
            {{ t(`editor.dish.picks.${pick}`, {}, {locale}) }}
          </button>
        </div>

        <p class="e-error" v-if="textError(error, 'badge')">{{ textError(error, 'badge') }}</p>
        <p class="e-help" v-else>{{ t('editor.dish.badge_help') }}</p>
      </div>
    </PanelField>

    <PanelField class="flex flex-col gap-2.5" field="sizes" v-slot="{selected}">
      <div class="flex flex-col gap-0.5">
        <div class="flex items-center gap-1.5">
          <p class="e-label">{{ t('editor.dish.sizes') }}</p>
          <SelectedInPreview v-if="selected"/>
        </div>

        <p class="flex items-center gap-1.5 text-xs/4 text-zinc-600">
          <ArrowUpNarrowWide class="size-3.5 text-zinc-500"/>
          {{ t('editor.dish.sorted') }}
        </p>
      </div>

      <div class="rounded-[10px] border border-zinc-200 overflow-hidden bg-white"
           v-for="(size, index) in draft.sizes" :key="size.key">
        <div class="flex items-center gap-2 py-1.5 pr-1.5 pl-3 bg-zinc-50 border-b border-[#f0f0f1]">
          <span class="size-5 shrink-0 flex items-center justify-center rounded-full bg-zinc-200 text-[11px] font-bold text-zinc-700">
            {{ index + 1 }}
          </span>

          <span class="flex-1 min-w-0 font-semibold truncate"
                :class="{'text-zinc-400': size.is_hidden}">
            {{ sizeTitle(size, index) }}
          </span>

          <span class="e-pill e-pill-sm bg-zinc-100 text-zinc-600" v-if="size.is_hidden">
            {{ t('editor.menus.hidden') }}
          </span>

          <button type="button"
                  class="e-icon-btn size-7"
                  :class="{'text-blue-700!': size.is_hidden}"
                  :aria-label="t(size.is_hidden ? 'editor.dish.show_size' : 'editor.dish.hide_size', {size: sizeTitle(size, index)})"
                  :title="!size.is_hidden && shownSizes === 1
                    ? t('editor.dish.last_shown')
                    : t(size.is_hidden ? 'editor.dish.show_size' : 'editor.dish.hide_size', {size: sizeTitle(size, index)})"
                  :disabled="!size.is_hidden && shownSizes === 1"
                  @click="toggleSize(size)">
            <Eye class="size-[15px]" v-if="size.is_hidden"/>
            <EyeOff class="size-[15px]" v-else/>
          </button>

          <button type="button"
                  class="e-icon-btn size-7"
                  :aria-label="t('editor.dish.remove_size', {number: index + 1})"
                  :title="t('editor.dish.remove_size', {number: index + 1})"
                  :disabled="draft.sizes.length === 1 || (!size.is_hidden && shownSizes === 1)"
                  @click="removeSize(size)">
            <Trash2 class="size-[15px]"/>
          </button>
        </div>

        <div class="grid grid-cols-2 gap-3 p-3"
             :class="{'opacity-60': size.is_hidden}">
          <div class="flex flex-col gap-1.5">
            <label class="e-label" :for="`size-${size.key}-weight`">{{ t('editor.dish.weight') }}</label>

            <div class="flex">
              <input class="e-input h-9 min-w-0 rounded-r-none text-end tabular-nums"
                     type="text"
                     inputmode="decimal"
                     maxlength="8"
                     :id="`size-${size.key}-weight`"
                     :class="{'e-changed': sizeChanged(size, 'weight')}"
                     :aria-invalid="!isNumber(size.weight, true)"
                     v-model="size.weight"/>

              <select class="e-input h-9 w-[70px] shrink-0 -ml-px pl-2.5 pr-7 rounded-l-none bg-[position:right_8px_center]"
                      :aria-label="t('editor.dish.unit', {number: index + 1})"
                      :class="{'e-changed': sizeChanged(size, 'weight_unit')}"
                      v-model="size.weight_unit">
                <option :value="unit" v-for="unit in UNITS" :key="unit">{{ t(`weight_unit.${unit}`) }}</option>
              </select>
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="e-label" :for="`size-${size.key}-price`">{{ t('editor.dish.price_label') }}</label>

            <div class="relative">
              <input class="e-input h-9 pr-7 text-end tabular-nums"
                     type="text"
                     inputmode="decimal"
                     maxlength="10"
                     :id="`size-${size.key}-price`"
                     :class="{'e-changed': sizeChanged(size, 'price')}"
                     :aria-invalid="!isNumber(size.price, false)"
                     v-model="size.price"
                     @blur="sortByPrice"/>
              <span class="absolute right-3 top-2 text-zinc-500" aria-hidden="true">{{ currency }}</span>
            </div>

            <p class="text-xs/4 text-[#a16207]" v-if="wasPrice(size)">{{ wasPrice(size) }}</p>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="e-label" :for="`size-${size.key}-time`">{{ t('editor.dish.time') }}</label>

            <div class="relative">
              <input class="e-input h-9 pr-11 text-end tabular-nums"
                     type="text"
                     inputmode="numeric"
                     maxlength="4"
                     :id="`size-${size.key}-time`"
                     :class="{'e-changed': sizeChanged(size, 'preparation_time')}"
                     :aria-invalid="!isWhole(size.preparation_time)"
                     v-model="size.preparation_time"/>
              <span class="absolute right-3 top-2 text-zinc-500" aria-hidden="true">{{ t('editor.dish.minutes') }}</span>
            </div>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="e-label" :for="`size-${size.key}-calories`">{{ t('editor.dish.calories') }}</label>

            <div class="relative">
              <input class="e-input h-9 pr-12 text-end tabular-nums"
                     type="text"
                     inputmode="numeric"
                     maxlength="6"
                     :id="`size-${size.key}-calories`"
                     :class="{'e-changed': sizeChanged(size, 'calories')}"
                     :aria-invalid="!isWhole(size.calories)"
                     v-model="size.calories"/>
              <span class="absolute right-3 top-2 text-zinc-500" aria-hidden="true">{{ t('editor.dish.kcal') }}</span>
            </div>
          </div>
        </div>

        <p class="px-3 pb-2.5 -mt-1 e-help" v-if="size.is_hidden">{{ t('editor.dish.size_hidden') }}</p>
        <p class="px-3 pb-2.5 -mt-1 e-error" v-if="sizeError(size)">{{ sizeError(size) }}</p>
      </div>

      <button type="button"
              class="h-10 flex items-center justify-center gap-1.5 border border-dashed border-zinc-400 rounded-[10px] text-zinc-700 font-semibold hover:bg-zinc-50 e-focus"
              v-if="draft.sizes.length < MAX_SIZES"
              @click="addSize">
        <Plus class="size-4"/>
        {{ t('editor.dish.add_size') }}
      </button>

      <p class="e-error" v-if="error('sizes')">{{ error('sizes') }}</p>
      <p class="e-help">{{ t('editor.dish.sizes_help') }}</p>
    </PanelField>

    <PanelField field="tags" v-slot="{selected}">
      <section class="flex flex-col gap-2.5">
        <div class="flex flex-col gap-0.5">
          <div class="flex items-center gap-1.5">
            <p class="e-label">{{ t('editor.dish.tags') }}</p>
            <SelectedInPreview v-if="selected"/>
          </div>
          <p class="e-help">{{ t('editor.dish.tags_help') }}</p>
        </div>

        <div class="flex flex-col gap-1.5"
             role="group"
             :aria-label="t(`editor.dish.tag_groups.${group}`)"
             v-for="group in TAG_GROUPS" :key="group">
          <p class="text-xs/4 font-semibold text-zinc-600">
            {{ t(`editor.dish.tag_groups.${group}`) }}<span class="font-normal text-zinc-500" v-if="group !== 'diet'"> · {{ t(`editor.dish.tag_hints.${group}`) }}</span>
          </p>

          <div class="flex flex-wrap gap-1.5">
            <!-- spiciness is picked one of: not spicy is none of its flags -->
            <button type="button"
                    class="h-8 inline-flex items-center gap-1.5 px-2.5 rounded-full border text-[13px] font-semibold e-focus"
                    :class="!spicy
                      ? 'border-zinc-900 bg-zinc-900 text-white'
                      : 'border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50'"
                    :aria-pressed="!spicy"
                    v-if="group === 'spiciness'"
                    @click="setNotSpicy">
              <Check class="size-[13px] stroke-3" v-if="!spicy"/>
              {{ t('editor.dish.not_spicy') }}
            </button>

            <button type="button"
                    class="h-8 inline-flex items-center gap-1.5 px-2.5 rounded-full border text-[13px] font-semibold e-focus"
                    :class="hasFlag(tag.key)
                      ? 'border-zinc-900 bg-zinc-900 text-white'
                      : 'border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50'"
                    :aria-pressed="hasFlag(tag.key)"
                    v-for="tag in tagsOf(group)" :key="tag.key"
                    @click="toggleTag(tag.key)">
              <Check class="size-[13px] stroke-3" v-if="hasFlag(tag.key)"/>
              <component :is="tag.icon" class="size-3.5" v-else/>
              {{ t(tag.label) }}
            </button>
          </div>
        </div>
      </section>
    </PanelField>

    <PanelField field="allergens" v-slot="{selected}">
      <section class="flex flex-col gap-2">
        <div class="flex items-center gap-1.5">
          <p class="e-label">{{ t('editor.dish.allergens') }}</p>
          <SelectedInPreview v-if="selected"/>
          <span class="e-help" :class="{'ml-auto': !selected}" v-if="allergens.length">{{ t('editor.dish.selected', {count: allergens.length}) }}</span>
        </div>

        <div class="flex flex-wrap gap-1.5">
          <button type="button"
                  class="h-8 inline-flex items-center gap-1.5 px-2.5 rounded-full border text-[13px] font-semibold e-focus"
                  :class="hasFlag(flag)
                    ? 'border-[#ca3500] bg-[#fbefeb] text-[#ca3500]'
                    : 'border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50'"
                  :aria-pressed="hasFlag(flag)"
                  v-for="flag in ALLERGENS" :key="flag"
                  @click="toggleFlag(flag)">
            <Check class="size-[13px] stroke-3" v-if="hasFlag(flag)"/>
            <Plus class="size-[13px]" v-else/>
            {{ t(getAllergenLabel(flag)) }}
          </button>
        </div>

        <p class="e-help">{{ t('editor.dish.allergens_help') }}</p>
      </section>
    </PanelField>
  </PanelShell>
</template>
