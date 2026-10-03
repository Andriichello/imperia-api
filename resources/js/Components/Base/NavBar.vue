<script setup lang="ts">
  import {Search} from "lucide-vue-next";
  import {PropType} from "vue";
  import {useI18n} from "vue-i18n";
  import LanguageButton from "@/Components/Base/LanguageButton.vue";
  import RestaurantButton from "@/Components/Base/RestaurantButton.vue";

  const props = defineProps({
    // Back to the restaurant page: the restaurant's button
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
    <!-- A long name is cut short, the buttons on the right keep their size -->
    <div class="min-w-0 flex">
      <RestaurantButton v-if="back"
                        @navigate="emits('on-back')"/>
    </div>

    <div class="shrink-0 flex gap-2">
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
