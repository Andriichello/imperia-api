<script setup lang="ts">
  import { X } from "lucide-vue-next";
  import { onUnmounted, watch } from "vue";

  const emits = defineEmits(['close']);

  const props = defineProps({
    open: {
      type: Boolean,
      required: true,
    },
    paddingTop: {
      type: Boolean,
      default: true,
    },
  });

  // Global (per-module) counter to handle multiple drawers
  let __openDrawerCount = 0;
  let __savedScrollY = 0;

  function lockBodyScroll(): void {
    if (typeof window === 'undefined') {
      return;
    }

    if (__openDrawerCount === 0) {
      __savedScrollY = window.scrollY || window.pageYOffset || 0;
      const body = document.body as HTMLBodyElement;
      body.style.position = 'fixed';
      body.style.top = `-${__savedScrollY}px`;
      body.style.left = '0';
      body.style.right = '0';
      body.style.width = '100%';
      body.style.overflow = 'hidden';
    }

    __openDrawerCount++;
  }

  function unlockBodyScroll(): void {
    if (typeof window === 'undefined') {
      return;
    }

    __openDrawerCount = Math.max(0, __openDrawerCount - 1);

    if (__openDrawerCount === 0) {
      const body = document.body as HTMLBodyElement;
      body.style.position = '';
      body.style.top = '';
      body.style.left = '';
      body.style.right = '';
      body.style.width = '';
      body.style.overflow = '';

      // Restore the previous scroll position
      window.scrollTo({ top: __savedScrollY });
    }
  }

  watch(
    () => props.open,
    (isOpen) => {
      if (isOpen) {
        lockBodyScroll();
      } else {
        unlockBodyScroll();
      }
    },
    { immediate: true }
  );

  function close(): void {
    emits('close');
  }

  onUnmounted(() => {
    // Ensure we unlock if the component unmounts while open
    if (props.open) {
      unlockBodyScroll();
    }
  });
</script>

<template>
  <transition name="slide">
    <div class="w-full fixed inset-0 z-50 flex justify-center"
         v-if="open"
         @click.self="close">
      <div class="bg-base-100 w-full max-w-md h-full max-h-full shadow-lg transition-transform transform translate-x-0 overflow-none relative"
           :class="{'pt-12': paddingTop}">
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
    transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .slide-enter-from,
  .slide-leave-to {
    transition: opacity 0.2s cubic-bezier(1, 0, 0.2, 0);
    opacity: 0;
  }
</style>
