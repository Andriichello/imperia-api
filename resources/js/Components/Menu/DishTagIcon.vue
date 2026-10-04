<script setup lang="ts">
  import {computed, type Component} from "vue";
  import {type TagTone, TAG_TONES} from "@/flags";

  /** The icon of a dish's tag (or of its allergens) in a circle of its color. */
  const props = defineProps<{
    icon: Component,
    tone: TagTone,
    // the dish page's size: 28px, the card's is 24px
    large?: boolean,
  }>();

  const colors = computed(() => TAG_TONES[props.tone]);
</script>

<template>
  <span class="shrink-0 rounded-full inline-flex items-center justify-center"
        :class="large ? 'size-7' : 'size-6'"
        :style="{background: colors.bg, color: colors.fg}">
    <component :is="icon" :class="large ? 'size-[15px]' : 'size-[13px]'" aria-hidden="true"/>
  </span>
</template>
