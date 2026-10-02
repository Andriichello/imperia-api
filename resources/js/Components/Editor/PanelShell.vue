<script setup lang="ts">
  import {PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {Check, ChevronLeft, ChevronRight, X} from 'lucide-vue-next'
  import type {Breadcrumb, LanguageTab, Selection} from '@/editor/sections'

  /**
   * The frame of every panel: breadcrumb, title (with actions), subtitle, tabs of the
   * content languages, the body and a footer with the save state, Discard and Save.
   */
  defineProps({
    breadcrumbs: {
      type: Array as PropType<Breadcrumb[]>,
      default: () => [],
    },
    title: {
      type: String,
      required: true,
    },
    subtitle: {
      type: String as PropType<string | null>,
      default: null,
    },
    // no tabs, when nothing in the panel is translated
    languages: {
      type: Array as PropType<LanguageTab[] | null>,
      default: null,
    },
    locale: {
      type: String as PropType<string | null>,
      default: null,
    },
    dirty: {
      type: Boolean,
      default: false,
    },
    saving: {
      type: Boolean,
      default: false,
    },
    // it's valid and nothing is being uploaded
    canSave: {
      type: Boolean,
      default: true,
    },
    saveLabel: {
      type: String as PropType<string | null>,
      default: null,
    },
  })

  const emits = defineEmits<{
    (e: 'navigate', selection: Selection | null): void
    (e: 'close'): void
    (e: 'update:locale', locale: string): void
    (e: 'discard'): void
    (e: 'save'): void
  }>()

  const {t} = useI18n()
</script>

<template>
  <div class="flex-1 min-h-0 flex flex-col">
    <div class="shrink-0 px-5 pt-3 pb-3.5 border-b border-[#f0f0f1]">
      <nav class="flex items-center gap-1 text-[13px]/[18px] text-zinc-500 whitespace-nowrap"
           :aria-label="t('editor.panel.breadcrumb')">
        <template v-for="(crumb, index) in breadcrumbs" :key="index">
          <ChevronRight class="size-3.5 shrink-0 text-zinc-400"
                        aria-hidden="true"
                        v-if="index > 0"/>

          <button type="button"
                  class="h-6 inline-flex items-center rounded hover:text-zinc-900 e-focus min-w-0"
                  :class="index === 0 ? 'gap-0.5 pr-1' : 'px-0.5'"
                  @click="emits('navigate', crumb.selection)">
            <ChevronLeft class="size-4 shrink-0" v-if="index === 0"/>
            <span class="truncate">{{ crumb.label }}</span>
          </button>
        </template>
      </nav>

      <div class="flex items-center gap-1.5 mt-1">
        <h2 class="flex-1 min-w-0 text-xl/7 font-semibold truncate">{{ title }}</h2>

        <slot name="actions"/>

        <button type="button"
                class="e-icon-btn"
                :aria-label="t('editor.panel.close')"
                :title="t('editor.panel.close')"
                @click="emits('close')">
          <X class="size-[18px]"/>
        </button>
      </div>

      <p class="mt-0.5 text-[13px]/[18px] text-zinc-500"
         v-if="subtitle">
        {{ subtitle }}
      </p>
    </div>

    <div class="shrink-0 flex items-stretch gap-4 px-5 border-b border-zinc-200"
         role="tablist"
         :aria-label="t('editor.panel.content_language')"
         v-if="languages && languages.length > 1">
      <button type="button"
              class="h-10 inline-flex items-center gap-1.5 -mb-px border-b-2 e-focus"
              :class="tab.locale === locale
                ? 'border-zinc-900 font-semibold'
                : 'border-transparent text-zinc-600 font-medium hover:text-zinc-900'"
              role="tab"
              :aria-selected="tab.locale === locale"
              v-for="tab in languages" :key="tab.locale"
              @click="emits('update:locale', tab.locale)">
        <span :lang="tab.locale">{{ tab.label }}</span>

        <span class="text-[11px] font-semibold text-zinc-500"
              v-if="tab.isDefault">
          {{ t('editor.panel.default') }}
        </span>

        <span class="e-pill e-pill-sm bg-amber-100 text-amber-800"
              v-else-if="tab.missing">
          {{ t('editor.panel.missing', {count: tab.missing}) }}
        </span>

        <span class="e-pill e-pill-sm bg-green-100 text-green-800"
              v-else-if="tab.missing === 0">
          {{ t('editor.panel.complete') }}
        </span>
      </button>
    </div>

    <div class="flex-1 min-h-0 overflow-y-auto p-5 flex flex-col gap-5">
      <slot/>
    </div>

    <div class="shrink-0 flex items-center gap-2 px-5 py-3 border-t border-zinc-200 bg-white">
      <span class="flex-1 min-w-0 inline-flex items-center gap-1.5 text-[13px]">
        <slot name="status">
          <template v-if="saving">
            <span class="text-zinc-500">{{ t('editor.panel.saving') }}</span>
          </template>

          <template v-else-if="dirty">
            <span class="size-[7px] rounded-full bg-amber-500" aria-hidden="true"/>
            <span class="text-[#a16207]">{{ t('editor.panel.unsaved') }}</span>
          </template>

          <template v-else>
            <Check class="size-4 text-[#00a63e]"/>
            <span class="text-zinc-500">{{ t('editor.panel.saved') }}</span>
          </template>
        </slot>
      </span>

      <button type="button"
              class="e-btn e-btn-secondary"
              :disabled="!dirty || saving"
              @click="emits('discard')">
        {{ t('editor.panel.discard') }}
      </button>

      <button type="button"
              class="e-btn e-btn-primary"
              :disabled="!dirty || !canSave || saving"
              @click="emits('save')">
        {{ saveLabel ?? t('editor.panel.save') }}
      </button>
    </div>
  </div>
</template>
