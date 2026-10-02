<script setup lang="ts">
  import {ChevronLeft, Search} from "lucide-vue-next";
  import {PropType} from "vue";
  import {useI18n} from "vue-i18n";
  import LanguageButton from "@/Components/Base/LanguageButton.vue";

  const props = defineProps({
    back: {
      type: Boolean as PropType<boolean>,
      default: false,
    },
  });

  const emits = defineEmits(['on-back', 'on-search', 'on-language']);

  const i18n = useI18n();
</script>

<template>
  <div class="w-full flex items-center justify-between gap-2">
    <div>
      <button type="button"
              class="size-11 flex items-center justify-center rounded border border-[#e8e8e8] bg-base-100 text-base-content cursor-pointer"
              :aria-label="i18n.t('nav.back')"
              v-if="back"
              @click="emits('on-back')">
        <ChevronLeft class="size-[22px]"/>
      </button>
    </div>

    <div class="flex gap-2">
      <LanguageButton @click="emits('on-language')"/>

      <button type="button"
              class="size-11 flex items-center justify-center rounded border border-[#e8e8e8] bg-base-100 text-base-content cursor-pointer"
              :aria-label="i18n.t('nav.search')"
              @click="emits('on-search')">
        <Search class="size-5"/>
      </button>
    </div>
  </div>
</template>
