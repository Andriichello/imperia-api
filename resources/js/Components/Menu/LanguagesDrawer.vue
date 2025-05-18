<script setup lang="ts">
  import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";

  defineProps({
    open: {
      type: Boolean,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    supported_locales: {
      type: Array as PropType<string[]>,
      required: true,
    }
  });

  const emits = defineEmits(['close', 'switch-menu', 'switch-category']);

  function close() {
    emits('close')
  }
</script>

<template>
  <BaseDrawer :open="open"
              @close="close">
    <div class="w-full h-full flex flex-col gap-2">
      <template v-for="l in supported_locales" :key="l">
        <div class="px-3 py-3 border-1 border-base-300 rounded flex justify-between">
          <h3 class="text-xl font-bold">
            {{ l }}
          </h3>

          <input class="checkbox"
                 type="checkbox"
                 :checked="l === locale">
        </div>
      </template>
    </div>
  </BaseDrawer>
</template>
