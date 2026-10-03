<script setup lang="ts">
  import {computed} from "vue";
  import {ChevronLeft} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";
  import {useAppStore} from "@/stores/app";
  import {restaurantUrl} from "@/reviews";

  /**
   * The restaurant's button, top-left on every public page but the restaurant page itself (a menu,
   * a dish, search, languages): a link to the restaurant page, with the name cut short when it's
   * long. A plain click is left to the page (`navigate`), which opens it without loading it again.
   */
  const emits = defineEmits(['navigate']);

  const i18n = useI18n();
  const app = useAppStore();

  const name = computed(() => app.restaurant?.name ?? '');

  const href = restaurantUrl();

  function onClick(event: MouseEvent) {
    // A new tab or window opens the link itself
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) {
      return;
    }

    event.preventDefault();
    emits('navigate');
  }
</script>

<template>
  <a class="h-11 min-w-0 max-w-60 flex-[0_1_auto] inline-flex items-center gap-0.5 pl-1.5 pr-3 rounded border border-[#e8e8e8] bg-base-100 text-base-content text-base/6 font-semibold"
     :href="href"
     :title="name"
     :aria-label="i18n.t('nav.restaurant_page', {name})"
     @click="onClick">
    <ChevronLeft class="size-5 shrink-0"/>
    <span class="min-w-0 truncate">{{ name }}</span>
  </a>
</template>
