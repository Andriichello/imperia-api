<script setup lang="ts">
  import {DishMenu, Dish, DishCategory} from "@/api";
  import CategoryInList from "@/Components/Menu/CategoryInList.vue";
  import {PropType} from "vue";

  const emits = defineEmits(['switch-menu', 'switch-category']);

  const props = defineProps({
    menu: {
      type: Object as PropType<DishMenu>,
      required: true,
    },
    products: {
      type: Array as PropType<Dish[]>,
      required: true,
    },
    closed: {
      type: Boolean,
      default: false,
    },
    establishment: {
      type: String as PropType<string | null>,
      default: 'restaurant',
    },
    currency: {
      type: String as PropType<string | null>,
      required: false,
      default: null,
    },
  });

  const categoryProducts = (menu: DishMenu, category: DishCategory) =>
    props.products.filter(
      (p: Dish) => p.category_id === category.id
    )

  const switchCategory = (category: DishCategory) => {
    emits('switch-category', category);
  }
</script>

<template>
  <div class="w-full flex flex-col">
    <div class="w-full flex flex-col">
      <div class="w-full flex flex-col text-center pb-0 px-3 cursor-pointer sticky"
           :class="{'pt-3': menu.description?.length > 0}"
           @click="emits('switch-menu', menu)">
        <p class="text-md font-light opacity-80"
           v-if="menu.description?.length > 0">
          {{ menu.description }}
        </p>
      </div>

      <template v-for="category in menu.categories" :key="category.id"
                v-if="!closed">
        <CategoryInList :category="category"
                        :products="categoryProducts(menu, category)"
                        :currency="currency"
                        :establishment="establishment"
                        @switch-category="switchCategory"/>

<!--        <div class="w-full h-[1px] bg-base-300"/>-->
      </template>
    </div>
  </div>
</template>
