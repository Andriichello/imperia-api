<script setup lang="ts">
  import {X} from "lucide-vue-next";
  import {Category, Menu, Product, Restaurant} from "@/api";
  import {PropType, ref} from "vue";
  import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";
  import SearchWithList from "@/Components/Menu/SearchWithList.vue";

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
    restaurant: {
      type: Object as PropType<Restaurant>,
      required: true,
    },
    open: {
      type: Boolean,
      required: true,
    },
  });

  const emits = defineEmits(['close', 'switch-menu', 'switch-category', 'open-menu', 'open-category', 'open-product']);

  const hasResults = ref(false);

  function close() {
    emits('close')
  }

  function setHasResults(has: boolean) {
    hasResults.value = has;
  }

  function openMenu(menu: Menu) {
    emits('open-menu', menu);
    close();
  }

  function openCategory(category: Category, menu: Menu) {
      emits('open-category', category, menu);
      close();
  }

  function openProduct(product: Product, category: Category, menu: Menu) {
    emits('open-product', product, category, menu);
    close();
  }
</script>

<template>
  <BaseDrawer :open="open"
              @close="close">
    <SearchWithList class="w-full"
                    :open="open"
                    :restaurant="restaurant"
                    :menus="menus"
                    :withPlacehoders="false"
                    @has-results-changed="setHasResults"
                    @open-menu="openMenu"
                    @open-category="openCategory"
                    @open-product="openProduct"/>

    <div class="w-full h-full flex flex-col px-6 overflow-auto"
         v-if="!hasResults">
      <template v-for="menu in menus" :key="menu.id">
        <div class="w-full flex flex-col text-start py-3 px-3 cursor-pointer"
             @click="emits('switch-menu', menu)">
          <h3 class="text-xl font-bold">
            {{ menu.title }}
          </h3>
          <p class="text-md font-light opacity-80">
            {{ menu.description?.length ? menu.description : 'menu' }}
          </p>
        </div>

        <div class="w-full h-[1px] bg-base-300"/>

        <div class="w-full flex flex-col pl-5">
          <template v-for="category in menu.categories" :key="category.id">
            <div class="w-full flex flex-col text-start py-3 px-3 cursor-pointer"
                 @click="emits('switch-category', category, menu)">
              <h3 class="text-lg font-bold">
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
  </BaseDrawer>
</template>
