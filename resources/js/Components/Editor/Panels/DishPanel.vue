<script setup lang="ts">
  import {computed, PropType, ref} from 'vue'
  import {useI18n} from 'vue-i18n'
  import axios from 'axios'
  import {
    Archive,
    ArrowLeft,
    Check,
    ChevronRight,
    Copy,
    Ellipsis,
    Flame,
    FolderInput,
    Image,
    Plus,
    Trash2,
    Upload,
  } from 'lucide-vue-next'
  import type {EditorCategory, EditorDish} from '@/api'
  import {
    EditorSizeWeightUnit,
    getEditorRestaurant,
    storeEditorDish,
    updateEditorDish,
    uploadEditorRestaurantPhoto,
  } from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import FieldLabel from '@/Components/Editor/Fields/FieldLabel.vue'
  import ToggleSwitch from '@/Components/Editor/Fields/ToggleSwitch.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {useContentLocale} from '@/composables/useContentLocale'
  import {useItemActions} from '@/composables/useItemActions'
  import {applyDish, DishDraft, dishOf, dishRequest, isListed, menusPreview, sizeOf} from '@/editor/menuDrafts'
  import type {Breadcrumb, Selection} from '@/editor/sections'
  import {translated} from '@/editor/translations'
  import {ALLERGENS, DISH_TAGS, getAllergenLabel} from '@/flags'
  import {useEditorStore} from '@/stores/editor'

  /**
   * A dish: whether guests see it, its photo, texts, sizes with prices, preparation time and
   * calories, diet tags and allergens. A new one is created in its category on Save.
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
  const PHOTO_TYPES = ['image/jpeg', 'image/png', 'image/webp']
  const MAX_PHOTO_SIZE = 10 * 1024 * 1024
  const UNITS = Object.values(EditorSizeWeightUnit)
  const QUICK_PICKS = ['new', 'bestseller', 'seasonal']
  const HOTNESS = ['low-hotness', 'medium-hotness', 'high-hotness', 'extreme-hotness']

  const restaurant = computed(() => editor.restaurant!)
  const dish = computed(() => props.selection.id ? editor.findDish(props.selection.id) : null)
  const category = computed<EditorCategory | null>(() => editor.findCategory(dish.value?.category_id ?? props.selection.parent ?? null))
  const menu = computed(() => category.value ? editor.findMenu(category.value.menu_id) : null)
  const isNew = computed(() => props.selection.id === null)

  let created: number | null = null

  const {draft, dirty, saving, failed, discard, save, error} = usePanelDraft<DishDraft>({
    saved: () => dishOf(dish.value, editor.locales),
    save: async (values) => {
      if (dish.value) {
        await updateEditorDish(dish.value.id, dishRequest(values, false))
      } else {
        created = (await storeEditorDish(category.value!.id, dishRequest(values, true))).data.data.id
      }

      return (await getEditorRestaurant(restaurant.value.id)).data.data
    },
    preview: (values, locale) => menusPreview(
      applyDish(restaurant.value.menus, props.selection.id, category.value?.id ?? 0, values),
      locale,
      editor.defaultLocale,
    ),
  })

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

  function sizeError(index: number): string | null {
    const size = draft.value.sizes[index]

    if (!isNumber(size.price, false)) {
      return t('editor.dish.price_needed')
    }

    if (!isNumber(size.weight, true)) {
      return t('editor.dish.size_number')
    }

    const saved = ['price', 'weight', 'weight_unit', 'calories', 'preparation_time']
      .map((key) => error(`sizes.${index}.${key}`))
      .find(Boolean)

    return saved ?? null
  }

  function addSize() {
    const last = draft.value.sizes[draft.value.sizes.length - 1]

    draft.value.sizes.push(sizeOf({
      weight_unit: (last?.weight_unit ?? 'g') as EditorDish['sizes'][number]['weight_unit'],
    }))
  }

  /** Different time and calories for each size, or the same for all (the first size's ones). */
  function setShared(shared: boolean) {
    if (shared) {
      draft.value.preparation_time = draft.value.sizes[0]?.preparation_time ?? ''
      draft.value.calories = draft.value.sizes[0]?.calories ?? ''
    } else {
      draft.value.sizes.forEach((size) => {
        size.preparation_time = draft.value.preparation_time
        size.calories = draft.value.calories
      })
    }

    draft.value.shared = shared
  }

  // Flags

  const tags = DISH_TAGS.filter((tag) => tag.key !== 'hotness')

  function hasFlag(flag: string): boolean {
    return draft.value.flags.includes(flag)
  }

  function toggleFlag(flag: string) {
    draft.value.flags = hasFlag(flag)
      ? draft.value.flags.filter((other) => other !== flag)
      : [...draft.value.flags, flag]
  }

  const spicy = computed(() => draft.value.flags.some((flag) => flag === 'hotness' || HOTNESS.includes(flag)))

  /** Spicy, at a level (none: not spicy). */
  function setHotness(level: string | null) {
    draft.value.flags = [
      ...draft.value.flags.filter((flag) => flag !== 'hotness' && !HOTNESS.includes(flag)),
      ...(level ? [level] : []),
    ]
  }

  const allergens = computed(() => draft.value.flags.filter((flag) => ALLERGENS.includes(flag)))

  // Photo

  const fileInput = ref<HTMLInputElement | null>(null)
  const uploading = ref<number | null>(null)
  const photoError = ref<string | null>(null)
  let upload: AbortController | null = null

  const photo = computed(() => draft.value.photos[0] ?? null)

  function photoUrl(): string | null {
    const item = photo.value

    return item ? (item.variants?.find((variant) => variant.extension === 'webp')?.url ?? item.url) : null
  }

  /** The new photo replaces the first one, it's shown once the dish is saved. */
  async function uploadPhoto(files: FileList | null) {
    const file = files?.[0]
    photoError.value = null

    if (fileInput.value) {
      fileInput.value.value = ''
    }

    if (!file) {
      return
    }

    if (!PHOTO_TYPES.includes(file.type)) {
      photoError.value = t('editor.photos.wrong_type', {name: file.name})
      return
    }

    if (file.size > MAX_PHOTO_SIZE) {
      photoError.value = t('editor.photos.too_big', {name: file.name})
      return
    }

    upload = new AbortController()
    uploading.value = 0

    try {
      const response = await uploadEditorRestaurantPhoto(restaurant.value.id, {file}, {
        signal: upload.signal,
        onUploadProgress: (event) => {
          uploading.value = event.total ? Math.round(event.loaded / event.total * 100) : 0
        },
      })

      draft.value.photos = [response.data.data, ...draft.value.photos.slice(1)]
    } catch (e) {
      if (!axios.isCancel(e)) {
        const reason = axios.isAxiosError(e) ? e.response?.data?.errors?.file?.[0] : null

        photoError.value = reason ?? t('editor.photos.upload_failed', {name: file.name})
      }
    } finally {
      uploading.value = null
      upload = null
    }
  }

  function removePhoto() {
    upload?.abort()
    draft.value.photos = draft.value.photos.slice(1)
  }

  // Saving

  const canSave = computed(() => !!category.value
    && uploading.value === null
    && !!draft.value.title[editor.defaultLocale]?.trim()
    && draft.value.sizes.every((size) => isNumber(size.price, false) && isNumber(size.weight, true)))

  async function onSave() {
    if (await save() && created) {
      editor.select({section: 'dish', id: created}, true)
    }
  }

  function onDiscard() {
    upload?.abort()
    photoError.value = null

    if (isNew.value) {
      editor.select(category.value ? {section: 'category', id: category.value.id} : null)
    } else {
      discard()
    }
  }

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
      editor.select(category.value ? {section: 'category', id: category.value.id} : null)
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
              :dirty="dirty || isNew"
              :saving="saving"
              :failed="failed"
              :can-save="canSave"
              :save-label="isNew ? t('editor.dish.create') : null"
              @navigate="editor.select($event, !!$event)"
              @close="editor.close()"
              @discard="onDiscard"
              @save="onSave">
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

    <div class="flex flex-col gap-1.5">
      <FieldLabel :label="t('editor.dish.photo')"/>

      <div class="flex items-center gap-3">
        <div class="relative size-24 shrink-0 rounded-lg overflow-hidden">
          <img class="size-full object-cover" :src="photoUrl()!" alt="" v-if="photoUrl()"/>

          <span class="size-full flex items-center justify-center rounded-lg border border-dashed border-zinc-300 text-zinc-400"
                aria-hidden="true"
                v-else>
            <Image class="size-6"/>
          </span>

          <span class="absolute inset-0 rounded-lg shadow-[inset_0_0_0_1px_rgba(0,0,0,0.06)]" aria-hidden="true"/>

          <div class="absolute inset-0 bg-white/80 flex flex-col items-center justify-center gap-1.5"
               v-if="uploading !== null">
            <span class="text-xs font-semibold text-zinc-700">{{ uploading }}%</span>
            <span class="w-[70%] h-1 rounded-sm bg-zinc-200 overflow-hidden">
              <span class="block h-full bg-blue-600" :style="{width: `${uploading}%`}"/>
            </span>
          </div>
        </div>

        <div class="flex flex-col items-start gap-2">
          <button type="button"
                  class="e-btn e-btn-secondary h-8"
                  :disabled="uploading !== null"
                  @click="fileInput?.click()">
            <Upload class="size-3.5"/>
            {{ photo ? t('editor.dish.replace') : t('editor.dish.upload') }}
          </button>

          <button type="button"
                  class="e-btn h-8 px-1 text-red-700"
                  v-if="photo || uploading !== null"
                  @click="removePhoto">
            <Trash2 class="size-3.5"/>
            {{ uploading !== null ? t('editor.photos.cancel_upload') : t('editor.dish.remove') }}
          </button>
        </div>

        <input class="hidden"
               type="file"
               accept="image/jpeg,image/png,image/webp"
               ref="fileInput"
               @change="uploadPhoto(($event.target as HTMLInputElement).files)"/>
      </div>

      <p class="e-error" v-if="photoError || error('media')">{{ photoError ?? error('media') }}</p>
      <p class="e-help" v-else>{{ t('editor.dish.photo_help') }}</p>
    </div>

    <div class="flex flex-col gap-1.5">
      <FieldLabel target="dish-title" :label="t('editor.dish.name')" :locale="locale"/>

      <input id="dish-title"
             class="e-input"
             type="text"
             maxlength="255"
             :placeholder="placeholder(draft.title)"
             :aria-invalid="!!textError(error, 'title')"
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
                v-model="draft.description[locale]"/>

      <p class="e-error" v-if="textError(error, 'description')">{{ textError(error, 'description') }}</p>
    </div>

    <div class="flex flex-col gap-1.5">
      <FieldLabel target="dish-badge" :label="t('editor.dish.badge')" :locale="locale"/>

      <input id="dish-badge"
             class="e-input"
             type="text"
             :maxlength="MAX_BADGE"
             :placeholder="placeholder(draft.badge)"
             :aria-invalid="!!textError(error, 'badge')"
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

    <section class="flex flex-col gap-2">
      <p class="e-label">{{ t('editor.dish.sizes') }}</p>

      <div class="flex flex-col gap-1"
           v-for="(size, index) in draft.sizes" :key="size.key">
        <div class="flex items-center gap-2">
          <input class="e-input flex-1 min-w-0 tabular-nums"
                 type="text"
                 inputmode="decimal"
                 maxlength="8"
                 :placeholder="t('editor.dish.size_placeholder')"
                 :aria-label="t('editor.dish.size', {number: index + 1})"
                 :aria-invalid="!isNumber(size.weight, true)"
                 v-model="size.weight"/>

          <select class="e-input w-[76px] shrink-0"
                  :aria-label="t('editor.dish.unit', {number: index + 1})"
                  v-model="size.weight_unit">
            <option :value="unit" v-for="unit in UNITS" :key="unit">{{ t(`weight_unit.${unit}`) }}</option>
          </select>

          <div class="relative w-[110px] shrink-0">
            <input class="e-input pr-7 text-end tabular-nums"
                   type="text"
                   inputmode="decimal"
                   maxlength="10"
                   :aria-label="t('editor.dish.price', {number: index + 1})"
                   :aria-invalid="!isNumber(size.price, false)"
                   v-model="size.price"/>
            <span class="absolute right-3 top-2.5 text-zinc-500" aria-hidden="true">{{ currency }}</span>
          </div>

          <button type="button"
                  class="e-icon-btn"
                  :aria-label="t('editor.dish.remove_size', {number: index + 1})"
                  :title="t('editor.dish.remove_size', {number: index + 1})"
                  :disabled="draft.sizes.length === 1"
                  :class="{'invisible': draft.sizes.length === 1}"
                  @click="draft.sizes.splice(index, 1)">
            <Trash2 class="size-[15px]"/>
          </button>
        </div>

        <!-- time and calories of each size -->
        <div class="grid grid-cols-2 gap-2 pr-10"
             v-if="!draft.shared">
          <div class="relative">
            <input class="e-input h-9 pr-11 tabular-nums"
                   type="text"
                   inputmode="numeric"
                   maxlength="4"
                   :aria-label="t('editor.dish.time_of', {number: index + 1})"
                   :aria-invalid="!isNumber(size.preparation_time, true)"
                   v-model="size.preparation_time"/>
            <span class="absolute right-3 top-2 text-zinc-500" aria-hidden="true">{{ t('editor.dish.minutes') }}</span>
          </div>

          <div class="relative">
            <input class="e-input h-9 pr-12 tabular-nums"
                   type="text"
                   inputmode="numeric"
                   maxlength="6"
                   :aria-label="t('editor.dish.calories_of', {number: index + 1})"
                   :aria-invalid="!isNumber(size.calories, true)"
                   v-model="size.calories"/>
            <span class="absolute right-3 top-2 text-zinc-500" aria-hidden="true">{{ t('editor.dish.kcal') }}</span>
          </div>
        </div>

        <p class="e-error" v-if="sizeError(index)">{{ sizeError(index) }}</p>
      </div>

      <button type="button"
              class="self-start h-8 inline-flex items-center gap-1.5 px-0.5 text-blue-600 font-semibold rounded e-focus"
              v-if="draft.sizes.length < MAX_SIZES"
              @click="addSize">
        <Plus class="size-4"/>
        {{ t('editor.dish.add_size') }}
      </button>

      <p class="e-error" v-if="error('sizes')">{{ error('sizes') }}</p>
      <p class="e-help">{{ t('editor.dish.sizes_help') }}</p>
    </section>

    <section class="flex flex-col gap-2">
      <div class="grid grid-cols-2 gap-3"
           v-if="draft.shared">
        <div class="flex flex-col gap-1.5">
          <FieldLabel target="dish-time" :label="t('editor.dish.time')"/>

          <div class="relative">
            <input id="dish-time"
                   class="e-input pr-11 tabular-nums"
                   type="text"
                   inputmode="numeric"
                   maxlength="4"
                   :aria-invalid="!isNumber(draft.preparation_time, true)"
                   v-model="draft.preparation_time"/>
            <span class="absolute right-3 top-2.5 text-zinc-500" aria-hidden="true">{{ t('editor.dish.minutes') }}</span>
          </div>
        </div>

        <div class="flex flex-col gap-1.5">
          <FieldLabel target="dish-calories" :label="t('editor.dish.calories')"/>

          <div class="relative">
            <input id="dish-calories"
                   class="e-input pr-12 tabular-nums"
                   type="text"
                   inputmode="numeric"
                   maxlength="6"
                   :aria-invalid="!isNumber(draft.calories, true)"
                   v-model="draft.calories"/>
            <span class="absolute right-3 top-2.5 text-zinc-500" aria-hidden="true">{{ t('editor.dish.kcal') }}</span>
          </div>
        </div>
      </div>

      <label class="self-start inline-flex items-center gap-2 text-[13px] text-zinc-700 cursor-pointer"
             v-if="draft.sizes.length > 1">
        <input class="size-4 accent-zinc-900"
               type="checkbox"
               :checked="!draft.shared"
               @change="setShared(!($event.target as HTMLInputElement).checked)"/>
        {{ t('editor.dish.per_size') }}
      </label>
    </section>

    <section class="flex flex-col gap-2">
      <p class="e-label">{{ t('editor.dish.diet') }}</p>

      <div class="flex flex-wrap gap-1.5">
        <button type="button"
                class="h-8 inline-flex items-center gap-1.5 px-2.5 rounded-full border text-[13px] font-semibold e-focus"
                :class="hasFlag(tag.key)
                  ? 'border-zinc-900 bg-zinc-900 text-white'
                  : 'border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50'"
                :aria-pressed="hasFlag(tag.key)"
                v-for="tag in tags" :key="tag.key"
                @click="toggleFlag(tag.key)">
          <component :is="tag.icon" class="size-3.5"/>
          {{ t(tag.label) }}
        </button>

        <button type="button"
                class="h-8 inline-flex items-center gap-1.5 px-2.5 rounded-full border text-[13px] font-semibold e-focus"
                :class="spicy
                  ? 'border-zinc-900 bg-zinc-900 text-white'
                  : 'border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50'"
                :aria-pressed="spicy"
                @click="setHotness(spicy ? null : 'medium-hotness')">
          <Flame class="size-3.5"/>
          {{ t('editor.dish.spicy') }}
        </button>
      </div>

      <div class="flex items-center gap-1.5"
           role="group"
           :aria-label="t('editor.dish.spiciness')"
           v-if="spicy">
        <span class="e-help mr-1">{{ t('editor.dish.spiciness') }}</span>

        <button type="button"
                class="h-7 px-2.5 rounded-full border text-xs font-semibold e-focus"
                :class="hasFlag(level)
                  ? 'border-zinc-900 bg-zinc-900 text-white'
                  : 'border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50'"
                :aria-pressed="hasFlag(level)"
                v-for="level in HOTNESS" :key="level"
                @click="setHotness(level)">
          {{ t(`editor.dish.hotness.${level}`) }}
        </button>
      </div>
    </section>

    <section class="flex flex-col gap-2">
      <div class="flex items-center">
        <p class="e-label">{{ t('editor.dish.allergens') }}</p>
        <span class="e-help ml-auto" v-if="allergens.length">{{ t('editor.dish.selected', {count: allergens.length}) }}</span>
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
  </PanelShell>
</template>
