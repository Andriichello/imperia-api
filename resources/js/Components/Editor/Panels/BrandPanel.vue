<script setup lang="ts">
  import {computed, onBeforeUnmount, watch} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {AlertTriangle, Check} from 'lucide-vue-next'
  import {updateEditorRestaurant} from '@/api'
  import PanelShell from '@/Components/Editor/PanelShell.vue'
  import FieldLabel from '@/Components/Editor/Fields/FieldLabel.vue'
  import {usePanelDraft} from '@/composables/usePanelDraft'
  import {
    BRAND_PRESETS,
    BrandColors,
    BrandPreset,
    contrastOnTints,
    formatContrast,
    isHex,
    presetOf,
    READABLE,
    readableContent,
  } from '@/editor/brand'
  import {useEditorStore} from '@/stores/editor'

  /**
   * Colors of the public pages: a preset or custom ones, whose text has to be readable on
   * the tints of the primary color. Both previews show them till they're saved.
   */
  const editor = useEditorStore()
  const {t} = useI18n()

  const restaurant = computed(() => editor.restaurant!)

  const {draft, dirty, saving, failed, discard, save, error} = usePanelDraft<BrandColors>({
    saved: () => ({...editor.brand}),
    save: async (colors) => (await updateEditorRestaurant(restaurant.value.id, {
      brand_primary: colors.primary.toLowerCase(),
      brand_primary_content: colors.content.toLowerCase(),
    })).data.data,
  })

  const valid = computed(() => isHex(draft.value.primary) && isHex(draft.value.content))

  const contrast = computed(() => valid.value ? contrastOnTints(draft.value) : null)

  const readable = computed(() => contrast.value !== null && contrast.value >= READABLE)

  // a darker text color, which would be readable
  const suggestion = computed(() => valid.value && !readable.value ? readableContent(draft.value) : null)

  const selected = computed(() => valid.value ? presetOf(draft.value) : null)

  function pick(preset: BrandPreset) {
    draft.value = {primary: preset.primary, content: preset.content}
  }

  /** The hex of the native color picker, or the typed one ("#3BB517"). */
  function setColor(key: keyof BrandColors, value: string) {
    const hex = value.trim().toLowerCase()

    // stored in lower case, shown in upper case
    draft.value[key] = hex.startsWith('#') ? hex : `#${hex}`
  }

  // the previews show the colors (valid ones only), and the saved ones again when it's left
  watch(draft, (colors) => {
    if (isHex(colors.primary) && isHex(colors.content)) {
      editor.previewBrand = {primary: colors.primary, content: colors.content}
    }
  }, {deep: true, immediate: true})

  onBeforeUnmount(() => {
    editor.previewBrand = null
  })
</script>

<template>
  <PanelShell :breadcrumbs="[{label: t('editor.panel.page_structure'), selection: null}]"
              :title="t('editor.sections.brand')"
              :subtitle="t('editor.subtitles.brand')"
              :dirty="dirty"
              :saving="saving"
              :failed="failed"
              :can-save="readable"
              :save-label="t('editor.brand.save')"
              @navigate="editor.close()"
              @close="editor.close()"
              @discard="discard"
              @save="save">
    <section class="flex flex-col gap-1.5">
      <h3 class="e-section mb-0.5">{{ t('editor.brand.presets_title') }}</h3>

      <button type="button"
              class="w-full flex items-center gap-3 py-[7px] px-3 rounded-lg border text-start e-focus"
              :class="selected?.key === preset.key
                ? 'border-blue-600 bg-blue-50 shadow-[0_0_0_1px_#2563eb]'
                : 'border-zinc-200 bg-white hover:bg-zinc-50'"
              :aria-pressed="selected?.key === preset.key"
              v-for="preset in BRAND_PRESETS" :key="preset.key"
              @click="pick(preset)">
        <span class="relative size-8 shrink-0 rounded-full shadow-[inset_0_0_0_1px_rgba(0,0,0,0.1)]"
              :style="{background: preset.primary}"
              aria-hidden="true">
          <span class="absolute -right-0.5 -bottom-0.5 size-3.5 rounded-full border-2 border-white"
                :style="{background: preset.content}"/>
        </span>

        <span class="flex-1 min-w-0 flex flex-col">
          <span class="font-semibold">{{ t('editor.brand.presets.' + preset.key) }}</span>
          <span class="text-xs text-zinc-500 tabular-nums">
            <span class="uppercase">{{ preset.primary }}</span>
            · {{ t('editor.brand.text') }}
            <span class="uppercase">{{ preset.content }}</span>
          </span>
        </span>

        <span class="text-xs font-semibold text-green-800 tabular-nums">
          {{ formatContrast(contrastOnTints(preset)) }} : 1
        </span>

        <span class="size-5 shrink-0 flex items-center justify-center rounded-full text-white"
              :class="selected?.key === preset.key ? 'bg-blue-600' : 'bg-transparent'"
              aria-hidden="true">
          <Check class="size-[13px] stroke-3"/>
        </span>
      </button>
    </section>

    <div class="h-px shrink-0 bg-[#f0f0f1]"/>

    <section class="flex flex-col gap-3">
      <h3 class="e-section">{{ t('editor.brand.custom') }}</h3>

      <div class="flex flex-col gap-1.5"
           v-for="key in (['primary', 'content'] as const)" :key="key">
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

          <input class="e-input tabular-nums uppercase"
                 type="text"
                 maxlength="7"
                 :id="`brand-${key}`"
                 :aria-invalid="!isHex(draft[key]) || !!error(`brand_${key === 'primary' ? 'primary' : 'primary_content'}`)"
                 :value="draft[key]"
                 @input="setColor(key, ($event.target as HTMLInputElement).value)"/>
        </div>

        <p class="e-error" v-if="!isHex(draft[key])">{{ t('editor.brand.invalid') }}</p>
        <p class="e-help" v-else>{{ t(`editor.brand.${key}_help`) }}</p>
      </div>

      <div class="flex items-center gap-2.5 py-2.5 px-3 rounded-lg border bg-green-50 border-green-200 text-green-800 text-[13px]/[18px]"
           role="status"
           v-if="readable">
        <Check class="size-4 shrink-0"/>
        <p class="flex-1">
          <b class="font-semibold">{{ t('editor.brand.readable') }}</b>
          {{ t('editor.brand.readable_text', {ratio: formatContrast(contrast ?? 0)}) }}
        </p>
      </div>

      <div class="flex items-start gap-2.5 py-2.5 px-3 rounded-lg border bg-red-50 border-red-200 text-red-800 text-[13px]/[18px]"
           role="alert"
           v-else-if="contrast !== null">
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

      <p class="e-error" v-if="error('brand_primary_content')">{{ error('brand_primary_content') }}</p>
    </section>
  </PanelShell>
</template>
