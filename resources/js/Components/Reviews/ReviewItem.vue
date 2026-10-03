<script setup lang="ts">
  import {computed, onMounted, PropType, ref} from "vue";
  import {useI18n} from "vue-i18n";
  import type {RestaurantReview} from "@/api";
  import StarRating from "@/Components/Reviews/StarRating.vue";
  import {reviewDate} from "@/reviews";

  /**
   * A review in the list: the guest's initial, name and when, the stars, and the text (as it was
   * written), cut at four lines with "Read more".
   */
  const props = defineProps({
    review: {
      type: Object as PropType<RestaurantReview>,
      required: true,
    },
  });

  const i18n = useI18n();

  const name = computed(() => props.review.name || i18n.t('reviews.guest'));
  const date = computed(() => reviewDate(props.review.created_at, i18n.locale.value, i18n.t('reviews.just_now')));

  const text = ref<HTMLElement | null>(null);
  // the text is cut, and whether it's shown in full
  const cut = ref(false);
  const expanded = ref(false);

  onMounted(() => {
    cut.value = !!text.value && text.value.scrollHeight > text.value.clientHeight + 1;
  });
</script>

<template>
  <article class="flex flex-col gap-2 py-4 border-t border-[#eeeeee]">
    <div class="flex items-center gap-2.5">
      <span class="size-9 shrink-0 flex items-center justify-center rounded-full bg-primary/15 text-primary-content text-[15px] font-bold uppercase"
            aria-hidden="true">
        {{ name.charAt(0) }}
      </span>

      <div class="flex-1 min-w-0 flex flex-col">
        <span class="text-base/[22px] font-semibold truncate">{{ name }}</span>
        <span class="text-[13px]/[18px] text-base-content/65">{{ date }}</span>
      </div>

      <StarRating :value="review.rating"/>
    </div>

    <template v-if="review.text">
      <p class="text-[15px]/[22px] text-zinc-800 whitespace-pre-line break-words"
         :class="{'line-clamp-4': !expanded}"
         :lang="review.locale ?? undefined"
         ref="text">
        {{ review.text }}
      </p>

      <button type="button"
              class="self-start min-h-6 text-[15px]/[22px] font-semibold text-primary-content cursor-pointer"
              :aria-expanded="expanded"
              v-if="cut"
              @click="expanded = !expanded">
        {{ i18n.t(expanded ? 'reviews.show_less' : 'reviews.read_more') }}
      </button>
    </template>
  </article>
</template>
