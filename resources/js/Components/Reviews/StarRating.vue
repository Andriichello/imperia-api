<script setup lang="ts">
  import {computed} from "vue";
  import {Star} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";
  import {averageFormatted} from "@/reviews";

  /**
   * Five stars filled to the rating in the brand's text color, the last one partly (4.6 is four
   * full stars and 60 % of the fifth); the empty ones are grey.
   */
  const props = defineProps({
    // from 0 to 5
    value: {
      type: Number,
      required: true,
    },
    // of a star, in pixels
    size: {
      type: Number,
      default: 16,
    },
    // color of the filled stars (the admin's ones aren't in the brand's color)
    filledClass: {
      type: String,
      default: 'text-primary-content',
    },
  });

  const i18n = useI18n();

  // the gap between stars, in pixels
  const GAP = 2;

  const style = computed(() => ({width: `${props.size}px`, height: `${props.size}px`}));

  // the filled part: whole stars with the gaps after them, and a part of the last one
  const width = computed(() => props.value * props.size + Math.max(0, Math.ceil(props.value) - 1) * GAP);

  const label = computed(() => i18n.t('reviews.stars', {
    rating: Number.isInteger(props.value) ? props.value : averageFormatted(props.value, i18n.locale.value),
  }));
</script>

<template>
  <span class="relative shrink-0 inline-flex gap-0.5"
        role="img"
        :aria-label="label">
    <Star class="shrink-0 fill-current stroke-none text-zinc-300"
          :style="style"
          v-for="n in 5" :key="n"/>

    <span class="absolute inset-y-0 left-0 flex gap-0.5 overflow-hidden"
          :style="{width: `${width}px`}">
      <Star class="shrink-0 fill-current stroke-none"
            :class="filledClass"
            :style="style"
            v-for="n in 5" :key="n"/>
    </span>
  </span>
</template>
