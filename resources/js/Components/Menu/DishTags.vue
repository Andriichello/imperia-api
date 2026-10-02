<script setup lang="ts">
  import {computed} from "vue";
  import {useI18n} from "vue-i18n";
  import {getDishTags} from "@/flags";

  const props = defineProps<{
    flags?: string[] | null,
  }>();

  const i18n = useI18n();

  const tags = computed(() => getDishTags(props.flags));
</script>

<template>
  <div class="flex flex-wrap gap-x-3 gap-y-0.5 normal-case text-base-content/60 opacity-70">
    <div class="flex flex-row justify-center items-center gap-1"
         v-for="tag in tags" :key="tag.key">
      <component :is="tag.icon" class="w-4 h-4"/>
      <p class="font-semibold pt-0.5">
        {{ i18n.t(tag.label) }}
      </p>
    </div>
  </div>
</template>
