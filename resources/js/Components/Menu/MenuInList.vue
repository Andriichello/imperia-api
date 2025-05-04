<script setup lang="ts">
import {Category, Menu, Product} from "@/api";
import CategoryInList from "@/Components/Menu/CategoryInList.vue";
import {PropType} from "vue";

  const emits = defineEmits(['switch-menu', 'switch-category']);

  const props = defineProps({
    menu: {
      type: Object as PropType<Menu>,
      required: true,
    },
    closed: {
      type: Boolean,
      default: false,
    }
  });

  const categoryProducts = (menu: Menu, category: Category) =>
    menu.products!.filter((p: Product) => p.category_ids.includes(category.id))

  const switchCategory = (category: Category) => {
    emits('switch-category', category);
  }
</script>

<template>
  <div class="w-full flex flex-col">
    <div class="w-full flex flex-col">
      <div class="w-full flex flex-col text-center py-3 px-3"
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

      <template v-for="category in menu.categories" :key="category.id"
                v-if="!closed">
        <CategoryInList :category="category"
                        :products="categoryProducts(menu, category)"
                        @switch-category="switchCategory"/>
      </template>

      <div class="w-full h-[1px] bg-base-300"/>

    </div>
  </div>
</template>
