<script setup lang="ts">
  import {computed} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {AlertTriangle, Check} from 'lucide-vue-next'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import FieldLabel from '@/Components/Editor/Fields/FieldLabel.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {
    accentOf,
    BRAND_PRESETS,
    BrandColors,
    BrandPreset,
    contrastOnList,
    contrastOnTints,
    formatContrast,
    isHex,
    nearAllergens,
    presetOf,
    PRICES_READABLE,
    READABLE,
    readableAccent,
    readableContent,
  } from '@/editor/brand'
  import {useEditorStore} from '@/stores/editor'

  /**
   * Colors of the public pages: a preset or custom ones, whose text has to be readable on
   * the tints of the primary color, and prices in the accent color on the menu (custom ones,
   * which aren't, can't be saved). Both previews show them till they're saved.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const {draft, error} = usePanelDraft<BrandColors>({section: 'brand', id: null})

  // a draft kept from before the accent color: the one between its two colors
  if (!draft.value.accent && isHex(draft.value.primary) && isHex(draft.value.content)) {
    draft.value.accent = accentOf(draft.value.primary, draft.value.content)
  }

  /** The draft's fields with their errors' fields. */
  const FIELDS: Record<keyof BrandColors, string> = {
    primary: 'brand_primary',
    content: 'brand_primary_content',
    accent: 'brand_accent',
  }

  const valid = computed(() => isHex(draft.value.primary) && isHex(draft.value.content))

  const contrast = computed(() => valid.value ? contrastOnTints(draft.value) : null)

  const readable = computed(() => contrast.value !== null && contrast.value >= READABLE)

  // a darker text color, which would be readable
  const suggestion = computed(() => valid.value && !readable.value ? readableContent(draft.value) : null)

  const accentContrast = computed(() => isHex(draft.value.accent) ? contrastOnList(draft.value.accent) : null)

  const accentReadable = computed(() => accentContrast.value !== null && accentContrast.value >= PRICES_READABLE)

  // a darker accent, in which prices would be readable
  const accentSuggestion = computed(() => accentContrast.value !== null && !accentReadable.value
    ? readableAccent(draft.value.accent)
    : null)

  const selected = computed(() => valid.value && isHex(draft.value.accent) ? presetOf(draft.value) : null)

  // allergen labels stand out less with an orange close to theirs
  const nearOrange = computed(() => nearAllergens(draft.value))

  /** "Green · #3BB517 · text #284625 · accent #327E1E" */
  const presetTitle = (preset: BrandPreset) => [
    t('editor.brand.presets.' + preset.key),
    preset.primary.toUpperCase(),
    `${t('editor.brand.text')} ${preset.content.toUpperCase()}`,
    `${t('editor.brand.accent_text')} ${preset.accent.toUpperCase()}`,
  ].join(' · ')

  function pick(preset: BrandPreset) {
    draft.value = {primary: preset.primary, content: preset.content, accent: preset.accent}
  }

  /** The hex of the native color picker, or the typed one ("#3BB517"). */
  function setColor(key: keyof BrandColors, value: string) {
    const hex = value.trim().toLowerCase()

    // stored in lower case, shown in upper case
    draft.value[key] = hex.startsWith('#') ? hex : `#${hex}`
  }

</script>

<template>
  <PanelShell :breadcrumbs="[{label: t('editor.panel.page_structure'), selection: null}]"
              :title="t('editor.sections.brand')"
              :subtitle="t('editor.subtitles.brand')"
              @navigate="editor.close()"
              @close="editor.close()">
    <section class="flex flex-col gap-2">
      <div class="flex items-baseline justify-between gap-2">
        <h3 class="e-section">{{ t('editor.brand.presets_title', {count: BRAND_PRESETS.length}) }}</h3>
        <p class="e-help">{{ t('editor.brand.presets_help') }}</p>
      </div>

      <div class="grid grid-cols-4 gap-2">
        <button type="button"
                class="relative min-w-0 flex flex-col items-center gap-1.5 pt-2.5 pb-2 px-1 rounded-[10px] border e-focus"
                :class="selected?.key === preset.key
                  ? 'border-blue-600 bg-blue-50 shadow-[0_0_0_1px_#2563eb]'
                  : 'border-zinc-200 bg-white hover:bg-zinc-50'"
                :aria-pressed="selected?.key === preset.key"
                :title="presetTitle(preset)"
                v-for="preset in BRAND_PRESETS" :key="preset.key"
                @click="pick(preset)">
          <span class="relative size-9 rounded-full shadow-[inset_0_0_0_1px_rgba(0,0,0,0.1)]"
                :style="{background: preset.primary}"
                aria-hidden="true">
            <span class="absolute -right-[3px] -bottom-[3px] size-4 rounded-full border-2 border-white"
                  :style="{background: preset.content}"/>
            <span class="absolute -left-[3px] -bottom-[3px] size-4 rounded-full border-2 border-white"
                  :style="{background: preset.accent}"/>
          </span>

          <span class="max-w-full text-xs/4 font-semibold truncate">{{ t('editor.brand.presets.' + preset.key) }}</span>

          <span class="absolute top-[5px] right-[5px] size-4 flex items-center justify-center rounded-full bg-blue-600 text-white"
                aria-hidden="true"
                v-if="selected?.key === preset.key">
            <Check class="size-[11px] stroke-3"/>
          </span>
        </button>
      </div>
    </section>

    <div class="h-px shrink-0 bg-[#f0f0f1]"/>

    <section class="flex flex-col gap-3">
      <div class="flex items-baseline justify-between gap-2">
        <h3 class="e-section">{{ t('editor.brand.fine_tune') }}</h3>
        <p class="e-help">{{ t('editor.brand.fine_tune_help') }}</p>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="min-w-0 flex flex-col gap-1.5"
             v-for="key in (['primary', 'content', 'accent'] as const)" :key="key">
          <FieldLabel :target="`brand-${key}`" :label="t(`editor.brand.${key}`)"/>

          <div class="flex gap-2">
            <!-- a click on the swatch opens the browser's color picker -->
            <label class="relative size-10 shrink-0 rounded-md shadow-[inset_0_0_0_1px_rgba(0,0,0,0.1)] cursor-pointer"
                   :style="{background: isHex(draft[key]) ? draft[key] : '#ffffff'}"
                   :title="t('editor.brand.pick')">
              <input class="absolute inset-0 size-full opacity-0 cursor-pointer"
                     type="color"
                     :aria-label="t('editor.brand.pick_for', {color: t(`editor.brand.${key}`)})"
                     :value="isHex(draft[key]) ? draft[key].toLowerCase() : '#ffffff'"
                     @input="setColor(key, ($event.target as HTMLInputElement).value)"/>
            </label>

            <input class="e-input min-w-0 tabular-nums uppercase"
                   type="text"
                   maxlength="7"
                   :id="`brand-${key}`"
                   :aria-invalid="!isHex(draft[key]) || !!error(FIELDS[key])"
                   :value="draft[key]"
                   @input="setColor(key, ($event.target as HTMLInputElement).value)"/>
          </div>

          <p class="e-error" v-if="!isHex(draft[key])">{{ t('editor.brand.invalid') }}</p>
          <p class="e-help" v-else>{{ t(`editor.brand.${key}_help`) }}</p>
        </div>
      </div>

      <div class="flex items-center gap-2.5 py-2.5 px-3 rounded-lg border bg-green-50 border-green-200 text-green-800 text-[13px]/[18px]"
           role="status"
           v-if="readable && accentReadable">
        <Check class="size-4 shrink-0"/>
        <p class="flex-1">
          <b class="font-semibold">
            {{ selected ? t('editor.brand.readable_preset', {name: t('editor.brand.presets.' + selected.key)}) : t('editor.brand.readable') }}
          </b>
          {{ t('editor.brand.readable_text', {ratio: formatContrast(contrast ?? 0)}) }}
          {{ t('editor.brand.readable_prices', {ratio: formatContrast(accentContrast ?? 0)}) }}
        </p>
      </div>

      <div class="flex items-start gap-2.5 py-2.5 px-3 rounded-lg border bg-red-50 border-red-200 text-red-800 text-[13px]/[18px]"
           role="alert"
           v-if="contrast !== null && !readable">
        <AlertTriangle class="size-4 shrink-0 mt-px"/>

        <div class="flex-1 flex flex-col items-start gap-2">
          <p>
            <b class="font-semibold">{{ t('editor.brand.hard_to_read') }}</b>
            {{ t('editor.brand.hard_to_read_text', {ratio: formatContrast(contrast)}) }}
          </p>

          <button type="button"
                  class="e-btn e-btn-secondary h-8 px-2.5"
                  v-if="suggestion"
                  @click="draft.content = suggestion">
            <span class="size-3.5 rounded-full shadow-[inset_0_0_0_1px_rgba(0,0,0,0.1)]"
                  :style="{background: suggestion}"
                  aria-hidden="true"/>
            {{ t('editor.brand.use_darker', {color: suggestion.toUpperCase()}) }}
          </button>
        </div>
      </div>

      <div class="flex items-start gap-2.5 py-2.5 px-3 rounded-lg border bg-red-50 border-red-200 text-red-800 text-[13px]/[18px]"
           role="alert"
           v-if="accentContrast !== null && !accentReadable">
        <AlertTriangle class="size-4 shrink-0 mt-px"/>

        <div class="flex-1 flex flex-col items-start gap-2">
          <p>
            <b class="font-semibold">{{ t('editor.brand.prices_hard_to_read') }}</b>
            {{ t('editor.brand.prices_hard_to_read_text', {ratio: formatContrast(accentContrast)}) }}
          </p>

          <button type="button"
                  class="e-btn e-btn-secondary h-8 px-2.5"
                  v-if="accentSuggestion"
                  @click="draft.accent = accentSuggestion">
            <span class="size-3.5 rounded-full shadow-[inset_0_0_0_1px_rgba(0,0,0,0.1)]"
                  :style="{background: accentSuggestion}"
                  aria-hidden="true"/>
            {{ t('editor.brand.use_darker', {color: accentSuggestion.toUpperCase()}) }}
          </button>
        </div>
      </div>

      <div class="flex items-start gap-2.5 py-2.5 px-3 rounded-lg border bg-[#fffbeb] border-[#fde68a] text-[#92400e] text-[13px]/[18px]"
           role="status"
           v-if="nearOrange">
        <AlertTriangle class="size-4 shrink-0 mt-px"/>
        <p class="flex-1">{{ t('editor.brand.near_allergens') }}</p>
      </div>

      <p class="e-error" v-if="error('brand_primary_content')">{{ error('brand_primary_content') }}</p>
      <p class="e-error" v-if="error('brand_accent')">{{ error('brand_accent') }}</p>
    </section>
  </PanelShell>
</template>
