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

  const emits = defineEmits(['close', 'switch-menu', 'switch-category']);

  function close() {
    emits('close')
  }
</script>

<template>
  <transition name="slide">
    <div class="fixed inset-0 z-50"
         v-if="open"
         @click.self="close">
      <div class="bg-white dark:bg-base-100 w-full h-full shadow-lg transition-transform transform translate-x-0 p-6 overflow-auto relative">
        <button class="absolute top-1.5 right-2 px-2 py-1 z-51"
                @click="close">
          <X/>
        </button>

        <div class="w-full flex flex-col gap-3">
          <template v-for="menu in menus" :key="menu.id">
            <div class="w-full flex flex-col text-start py-3 px-3 cursor-pointer"
                 @click="emits('switch-menu', menu)">
              <h3 class="text-xl font-bold">
                {{ menu.title }}
              </h3>
              <p class="text-md font-light opacity-80"
                 v-if="menu!.description?.length">
                {{ menu.description }}
              </p>
            </div>

            <div class="w-full h-[1px] bg-base-300"/>

            <div class="w-full flex flex-col pl-5">
              <template v-for="category in menu.categories" :key="category.id">
                <div class="w-full flex flex-col text-start py-3 px-3 cursor-pointer"
                     @click="emits('switch-category', category, menu)">
                  <h3 class="text-xl font-bold">
                    {{ category.title }}
                  </h3>
                  <p class="text-md font-light opacity-80"
                     v-if="category!.description?.length">
                    {{ category.description }}
                  </p>
                </div>
              </template>
            </div>

            <div class="w-full h-[1px] bg-base-300"/>
          </template>
        </div>
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
