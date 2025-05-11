<script setup lang="ts">
  import {X} from "lucide-vue-next";
  import {Menu} from "@/api";
  import {PropType} from "vue";

  defineProps({
    menuId: {
      type: Number,
      required: true,
    },
    categoryId: {
      type: Number as PropType<number | null>,
      required: false,
      default: null,
    },
    menus: {
      type: Array as PropType<Menu[]>,
      required: true,
    },
    open: {
      type: Boolean,
      required: true,
    },
  });

  const emits = defineEmits(['close']);

  function close() {
    emits('close')
  }
</script>

<template>
  <transition name="slide">
    <div
      v-if="open"
      class="fixed inset-0 z-50"
      @click.self="close"
    >
      <div class="bg-white dark:bg-base-100 w-full h-full shadow-lg transition-transform transform translate-x-0 p-6 overflow-auto relative">
        <button class="absolute top-3 right-4 text-xl" @click="close">
          <X/>
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
