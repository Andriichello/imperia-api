<script setup lang="ts">
  import {useI18n} from 'vue-i18n'
  import {Check, ChevronDown, Globe} from 'lucide-vue-next'
  import DropdownMenu from '@/Components/Editor/DropdownMenu.vue'
  import {INTERFACE_LOCALES, languageName, saveInterfaceLocale} from '@/editor/translations'

  /**
   * The admin's own language (not the one of the restaurant's content), remembered on this browser.
   */
  const {t, locale} = useI18n()

  function pick(value: string) {
    locale.value = value
    document.documentElement.lang = value
    saveInterfaceLocale(value)
  }
</script>

<template>
  <DropdownMenu align="end">
    <template #trigger="{open, toggle}">
      <button type="button"
              class="e-nav-btn text-zinc-700"
              aria-haspopup="menu"
              :aria-expanded="open"
              :aria-label="t('admin.language', {language: languageName(locale)})"
              :title="t('admin.language_title')"
              @click="toggle">
        <Globe class="size-4 text-zinc-500"/>
        <span class="max-sm:sr-only">{{ languageName(locale) }}</span>
        <ChevronDown class="size-3.5 text-zinc-400"/>
      </button>
    </template>

    <button type="button"
            class="e-dropdown-item"
            role="menuitemradio"
            :aria-checked="locale === item"
            v-for="item in INTERFACE_LOCALES" :key="item"
            @click="pick(item)">
      <span class="flex-1" :lang="item">{{ languageName(item) }}</span>
      <Check class="size-4 text-zinc-500" v-if="locale === item"/>
    </button>
  </DropdownMenu>
</template>
