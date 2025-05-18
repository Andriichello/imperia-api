<script setup lang="ts">
  import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";

  defineProps({
    open: {
      type: Boolean,
      required: true,
    },
  });

  const emits = defineEmits(['close', 'switch-menu', 'switch-category']);

  function close() {
    emits('close')
  }
</script>

<template>
  <BaseDrawer :open="open"
              @close="close">
    <div class="w-full h-full flex flex-col bg-green-300">
      <p>
        Search...
      </p>
    </div>
  </BaseDrawer>
</template>
