<script setup lang="ts">
  import { X } from "lucide-vue-next";
  import { useI18n } from "vue-i18n";
  import { useScrollLock } from "@/composables/useScrollLock";

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

  const i18n = useI18n();

  // The page doesn't scroll while the drawer is open
  useScrollLock(() => props.open);

  function close(): void {
    emits('close');
  }
</script>

<template>
  <transition name="slide">
    <div class="w-full fixed inset-0 z-50 flex justify-center"
         v-if="open"
         @click.self="close">
      <div class="bg-base-100 w-full max-w-md h-full max-h-full shadow-lg transition-transform transform translate-x-0 relative"
           :class="{'pt-15': paddingTop}">
        <!-- Buttons of the drawer (if any), then the close button -->
        <div class="absolute top-2 right-2 z-51 flex gap-2">
          <slot name="actions"/>

          <button type="button"
                  class="size-11 flex items-center justify-center rounded border border-[#e8e8e8] bg-base-100 text-base-content cursor-pointer"
                  :aria-label="i18n.t('drawer.close')"
                  @click="close">
            <X class="size-[22px]"/>
          </button>
        </div>

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
