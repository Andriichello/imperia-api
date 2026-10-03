<script setup lang="ts">
  import {PropType} from 'vue'
  import {useI18n} from 'vue-i18n'
  import {ChevronRight, X} from 'lucide-vue-next'
  import type {Breadcrumb, LanguageTab, Selection} from '@/editor/sections'

  /**
   * The frame of every panel: breadcrumb, title (with actions and Close), subtitle, tabs of
   * the content languages and the body. Changes are saved with the editor's save bar under it.
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
    // the page structure can't be closed
    closable: {
      type: Boolean,
      default: true,
    },
    // padding and gaps of the body
    bodyClass: {
      type: String,
      default: 'p-5 gap-5',
    },
  })

  const emits = defineEmits<{
    (e: 'navigate', selection: Selection | null): void
    (e: 'close'): void
    (e: 'update:locale', locale: string): void
  }>()

  const {t} = useI18n()
</script>

<template>
  <div class="flex-1 min-h-0 flex flex-col">
    <div class="shrink-0 px-5 pt-3 pb-3.5 border-b border-[#f0f0f1]">
      <slot name="breadcrumb">
        <nav class="h-6 flex items-center gap-1 text-[13px]/[18px] text-zinc-500 whitespace-nowrap"
             :aria-label="t('editor.panel.breadcrumb')">
          <template v-for="(crumb, index) in breadcrumbs" :key="index">
            <ChevronRight class="size-3.5 shrink-0 text-zinc-400"
                          aria-hidden="true"
                          v-if="index > 0"/>

            <button type="button"
                    class="h-6 inline-flex items-center rounded hover:text-zinc-900 e-focus min-w-0"
                    :class="index === 0 ? 'pr-0.5' : 'px-0.5'"
                    @click="emits('navigate', crumb.selection)">
              <span class="truncate">{{ crumb.label }}</span>
            </button>
          </template>
        </nav>
      </slot>

      <div class="min-h-8 flex items-center gap-1.5 mt-1">
        <h2 class="flex-1 min-w-0 text-xl/7 font-semibold truncate">{{ title }}</h2>

        <slot name="actions"/>

        <button type="button"
                class="e-icon-btn"
                :aria-label="t('editor.panel.close')"
                :title="t('editor.panel.close')"
                v-if="closable"
                @click="emits('close')">
          <X class="size-[18px]"/>
        </button>
      </div>

      <p class="mt-0.5 text-[13px]/[18px] text-zinc-500"
         v-if="subtitle">
        {{ subtitle }}
      </p>
    </div>

    <div class="shrink-0 flex items-stretch gap-[18px] px-5 border-b border-zinc-200"
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

        <!-- only while something isn't translated -->
        <span class="e-pill e-pill-sm bg-amber-100 text-amber-800 tabular-nums"
              :title="t('editor.panel.translated', {filled: tab.filled, total: tab.total})"
              v-if="!tab.isDefault && tab.filled < tab.total">
          {{ tab.filled }}/{{ tab.total }}
        </span>
      </button>
    </div>

    <slot name="notice"/>

    <div class="flex-1 min-h-0 overflow-y-auto flex flex-col"
         :class="bodyClass"
         data-panel-body>
      <slot/>
    </div>
  </div>
</template>
