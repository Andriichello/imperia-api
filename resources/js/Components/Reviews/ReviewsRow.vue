<script setup lang="ts">
  import {computed} from "vue";
  import {ChevronRight, Star} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";
  import StarRating from "@/Components/Reviews/StarRating.vue";
  import {useAppStore} from "@/stores/app";
  import {averageFormatted, restaurantUrl} from "@/reviews";

  /**
   * The restaurant's reviews in its info list: their average and count (approved ones only), or
   * that there are none yet. The whole row opens the reviews page.
   */
  const i18n = useI18n();
  const app = useAppStore();

  const summary = computed(() => app.reviews);
  const count = computed(() => summary.value?.count ?? 0);
  const average = computed(() => averageFormatted(summary.value?.average ?? 0, i18n.locale.value));
  const countLabel = computed(() => i18n.t('reviews.count', {count: count.value}, count.value));

  const label = computed(() => count.value
    ? i18n.t('reviews.row_label', {average: average.value, count: countLabel.value})
    : i18n.t('reviews.row_label_none'));
</script>

<template>
  <div class="w-full flex flex-col mt-1">
    <div class="w-full h-px bg-base-300"/>

    <a class="w-full flex justify-start items-start gap-3 py-2 px-3 text-start"
       :href="restaurantUrl('/reviews')"
       :aria-label="label">
      <span class="size-12 min-w-12 flex justify-center items-center bg-primary/15 border border-primary/40 text-primary-content rounded">
        <Star class="size-6"/>
      </span>

      <span class="grow min-w-0 min-h-12 flex flex-col justify-center items-start">
        <span class="text-sm/5 font-semibold text-base-content/65">
          {{ i18n.t('reviews.title') }}
        </span>

        <!-- the average and the count stay together, when the line wraps -->
        <span class="flex flex-wrap items-center gap-x-1.5 text-base/6"
              v-if="count">
          <StarRating :value="summary!.average ?? 0"/>
          <span class="whitespace-nowrap">
            <span class="font-semibold">{{ average }}</span>
            <span class="text-base-content/72"> · {{ countLabel }}</span>
          </span>
        </span>

        <span class="text-base/6" v-else>
          <span class="font-semibold">{{ i18n.t('reviews.none') }}</span>
          <span class="text-base-content/72"> · {{ i18n.t('reviews.be_first') }}</span>
        </span>
      </span>

      <span class="size-12 min-w-12 mr-1.5 flex justify-center items-center text-base-content/65">
        <ChevronRight class="size-6"/>
      </span>
    </a>

    <div class="w-full h-px bg-base-300"/>
  </div>
</template>
