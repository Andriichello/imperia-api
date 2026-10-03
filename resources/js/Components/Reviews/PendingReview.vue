<script setup lang="ts">
  import {computed, PropType} from "vue";
  import {Clock} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";
  import type {RestaurantReview} from "@/api";
  import StarRating from "@/Components/Reviews/StarRating.vue";
  import {useAppStore} from "@/stores/app";
  import {reviewDate} from "@/reviews";

  /**
   * A review the guest left, which waits for the restaurant's approval: only they see it, on their
   * device. On the reviews page it says so; right after it's posted, it's shown as it is.
   */
  const props = defineProps({
    review: {
      type: Object as PropType<RestaurantReview>,
      required: true,
    },
    // right after it's posted: without the title and the note
    posted: {
      type: Boolean,
      default: false,
    },
  });

  const i18n = useI18n();
  const app = useAppStore();

  const name = computed(() => props.review.name || i18n.t('reviews.guest'));
  const date = computed(() => reviewDate(props.review.created_at, i18n.locale.value, i18n.t('reviews.just_now')));
</script>

<template>
  <section class="flex flex-col gap-2 py-3.5 px-4 border border-dashed border-zinc-400 rounded-lg bg-zinc-50 text-start"
           :aria-label="i18n.t('reviews.yours')">
    <div class="flex items-center justify-between gap-2"
         v-if="!posted">
      <h2 class="text-[15px]/[22px] font-bold">{{ i18n.t('reviews.yours') }}</h2>

      <span class="h-6 shrink-0 inline-flex items-center gap-1 pr-2.5 pl-2 rounded-full bg-zinc-100 text-zinc-700 text-xs/4 font-semibold whitespace-nowrap">
        <Clock class="size-3.5"/>
        {{ i18n.t('reviews.waiting') }}
      </span>
    </div>

    <div class="flex items-center gap-2">
      <span class="min-w-0 font-semibold truncate">{{ name }}</span>
      <span class="shrink-0 text-[13px] text-base-content/65">· {{ date }}</span>

      <span class="ml-auto h-6 shrink-0 inline-flex items-center gap-1 pr-2.5 pl-2 rounded-full bg-zinc-100 text-zinc-700 text-xs/4 font-semibold whitespace-nowrap"
            v-if="posted">
        <Clock class="size-3.5"/>
        {{ i18n.t('reviews.waiting') }}
      </span>
      <StarRating class="ml-auto" :value="review.rating" v-else/>
    </div>

    <StarRating :value="review.rating" v-if="posted"/>

    <p class="text-[15px]/[22px] text-zinc-800 whitespace-pre-line break-words"
       v-if="review.text">
      {{ review.text }}
    </p>

    <p class="text-[13px]/[18px] text-base-content/65"
       v-if="!posted">
      {{ i18n.t('reviews.yours_note', {name: app.restaurant?.name ?? ''}) }}
    </p>
  </section>
</template>
