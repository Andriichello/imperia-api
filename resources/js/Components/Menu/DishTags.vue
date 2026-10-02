<script setup lang="ts">
  import {computed} from "vue";
  import {useI18n} from "vue-i18n";
  import {getDishTags} from "@/flags";

  const props = withDefaults(defineProps<{
    flags?: string[] | null,
    iconClass?: string,
  }>(), {
    iconClass: 'size-3.5',
  });

  const i18n = useI18n();

  const tags = computed(() => getDishTags(props.flags));
</script>

<template>
  <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 font-semibold text-primary-content"
       v-if="tags.length">
    <span class="flex items-center gap-1"
          v-for="tag in tags" :key="tag.key">
      <component :is="tag.icon" class="shrink-0" :class="iconClass"/>
      {{ i18n.t(tag.label) }}
    </span>
  </div>
</template>
