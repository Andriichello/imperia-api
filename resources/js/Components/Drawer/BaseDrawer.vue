<script setup lang="ts">
  import {X} from "lucide-vue-next";

  const emits = defineEmits(['close']);

  defineProps({
    open: {
      type: Boolean,
      required: true,
    },
  });


  function close() {
    emits('close')
  }
</script>

<template>
  <transition name="slide">
    <div class="w-full fixed inset-0 z-50 flex justify-center"
         v-if="open"
         @click.self="close">
      <div class="bg-base-100 w-full max-w-md h-full max-h-full shadow-lg transition-transform transform translate-x-0 pt-12 overflow-none relative">
        <button class="absolute top-1.5 right-2 z-51 btn btn-sm h-9 bg-base-100"
                @click="close">
          <X class="w-5 h-5 text-base-content/80"/>
        </button>

        <slot/>
      </div>
    </div>
  </transition>
</template>

<style scoped>
  .slide-enter-active,
  .slide-leave-active {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .slide-enter-from,
  .slide-leave-to {
    transform: translateX(100%);
  }
</style>
