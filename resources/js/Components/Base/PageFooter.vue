<script setup lang="ts">
  import {computed} from "vue";
  import {Info, Mail} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";
  import {useAppStore} from "@/stores/app";

  /**
   * The end of the restaurant, menu and reviews pages (at the bottom of short ones): where allergen
   * and diet info comes from, and the developer's contact.
   */
  const i18n = useI18n();
  const app = useAppStore();

  const name = computed(() => app.restaurant?.name ?? '');
</script>

<template>
  <footer class="w-full mt-auto flex flex-col gap-5 pt-6 px-5 pb-8 bg-[#f0f0f1] border-t border-zinc-200 text-sm/5 text-base-content/72 text-start">
    <p class="flex items-start gap-2">
      <Info class="size-4 shrink-0 mt-0.5"/>
      <span>{{ i18n.t('footer.allergens', {name}) }}</span>
    </p>

    <template v-if="app.developer_email">
      <div class="h-px bg-zinc-200"/>

      <div class="flex flex-col gap-0.5">
        <p>{{ i18n.t('footer.work_together') }}</p>

        <a class="self-start min-h-11 inline-flex items-center gap-2 text-base/6 font-semibold text-base-content underline underline-offset-3"
           :href="`mailto:${app.developer_email}`">
          <Mail class="size-[18px] shrink-0"/>
          {{ app.developer_email }}
        </a>
      </div>
    </template>
  </footer>
</template>
