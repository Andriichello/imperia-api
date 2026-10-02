<script setup lang="ts">
  import {Languages} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";

  const i18n = useI18n();
</script>

<template>
  <!-- Opens the language drawer; shows the current language's code -->
  <button type="button"
          class="h-11 inline-flex items-center justify-center gap-1.5 px-3 rounded border border-[#e8e8e8] bg-base-100 text-base-content text-sm font-semibold cursor-pointer"
          :aria-label="i18n.t('nav.language', {name: i18n.t('languages.names.' + i18n.locale.value)})">
    <Languages class="size-5"/>
    <span>{{ i18n.locale.value.toUpperCase() }}</span>
  </button>
</template>
