<script setup lang="ts">
  import {DishMenu, Dish, DishCategory} from "@/api";
  import CategoryInList from "@/Components/Menu/CategoryInList.vue";
  import {editKey} from "@/editor/editKey";
  import {computed, PropType} from "vue";

  /**
   * A menu with its categories and dishes. What comes after them (the slot, e.g. the page's footer)
   * is at the bottom of the last category.
   */
  const emits = defineEmits(['switch-menu', 'switch-category', 'open-product']);

  const props = defineProps({
    menu: {
      type: Object as PropType<DishMenu | null>,
      required: false,
      default: null,
    },
    products: {
      type: Array as PropType<Dish[] | null>,
      required: false,
      default: () => [],
    },
    closed: {
      type: Boolean,
      default: false,
    },
    currency: {
      type: String as PropType<string | null>,
      required: false,
      default: null,
    },
  });

  const lastCategory = computed<DishCategory | null>(() => props.menu?.categories?.at(-1) ?? null);

  const categoryProducts = (menu: DishMenu, category: DishCategory) =>
    (props.products ?? []).filter(
      (p: Dish) => p.category_id === category.id
    )

  const switchCategory = (category: DishCategory) => {
    emits('switch-category', category);
  }

  const openProduct = ({product, category, menu}: { product: Dish, category: DishCategory, menu: DishMenu}) => {
    emits('open-product', { product, category, menu });
  }
</script>

<template>
  <div class="w-full flex flex-col" v-if="menu">
    <div class="w-full flex flex-col">
      <div class="w-full flex flex-col text-center pb-0 px-4 cursor-pointer sticky"
           :class="{'pt-3': menu?.description?.length > 0}"
           @click="emits('switch-menu', menu)">
        <p class="text-[15px]/[22px] text-base-content/65"
           v-bind="editKey('menu:' + menu.id)"
           v-if="menu?.description?.length > 0">
          {{ menu?.description }}
        </p>
      </div>

      <template v-if="!closed && lastCategory">
        <CategoryInList :category="category"
                        :products="categoryProducts(menu, category)"
                        :currency="currency"
                        :first="index === 0"
                        @switch-category="switchCategory"
                        @open-product="openProduct"
                        v-for="(category, index) in menu.categories.slice(0, -1)" :key="category.id"/>

        <!-- The last category and what's after it fill the screen below the sticky menus (92px tall,
             plus a 10px gap), so it can be scrolled up under them -->
        <div class="w-full min-h-[calc(100dvh-102px)] flex flex-col">
          <CategoryInList :category="lastCategory"
                          :products="categoryProducts(menu, lastCategory)"
                          :currency="currency"
                          :first="menu.categories.length === 1"
                          :key="lastCategory.id"
                          @switch-category="switchCategory"
                          @open-product="openProduct"/>

          <slot/>
        </div>
      </template>

      <slot v-else/>
    </div>
  </div>
</template>
