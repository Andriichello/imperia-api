<script setup lang="ts">
  import {Dish, DishCategory} from "@/api";
  import {PropType} from "vue";
  import {useI18n} from "vue-i18n";
  import DishCard from "@/Components/Menu/DishCard.vue";
  import {editKey} from "@/editor/editKey";

  const emits = defineEmits(['switch-category', 'open-product']);

  const props = defineProps({
    category: {
      type: Object as PropType<DishCategory>,
      required: true,
    },
    products: {
      type: Array as PropType<Dish[]>,
      required: true,
    },
    preview: {
      type: Boolean as PropType<boolean>,
      default: false,
    },
    currency: {
      type: String as PropType<string | null>,
      required: false,
      default: null,
    },
    // The first one is closer to what's above it than categories are to each other
    first: {
      type: Boolean as PropType<boolean>,
      default: false,
    },
  });

  const i18n = useI18n();

  function onProductClick(product: Dish) {
    emits('open-product', { product, category: props.category, menu: props.category.menu });
  }
</script>

<template>
  <section class="w-full flex flex-col px-2"
           :class="first ? 'mt-4' : 'mt-6'"
           :id="'category-' + category.id">
    <div class="w-full flex flex-col text-center py-2.5 px-3 bg-primary/18 border border-primary/45 rounded-t-xl cursor-pointer"
         v-bind="editKey('category:' + category.id)"
         @click="emits('switch-category', category)">
      <h2 class="text-[22px]/[30px] font-semibold text-primary-content">
        {{ category.title }}
      </h2>
      <p class="text-[15px]/[22px] text-base-content/65"
         v-if="category.description?.length">
        {{ category.description }}
      </p>
    </div>

    <template v-if="!products!.length">
      <div class="w-full flex flex-col text-center p-2">
        <p class="text-[15px]/[22px] text-base-content/65">{{ i18n.t('menu.empty_category') }}</p>
      </div>
    </template>

    <div class="w-full flex flex-col"
         :id="'category-' + category.id + '-products'"
         v-else>
      <template v-for="product in products" :key="product.id">
        <DishCard class="cursor-pointer"
                  v-bind="editKey('dish:' + product.id)"
                  :product="product"
                  :currency="currency"
                  @click="onProductClick(product)"/>

        <div class="h-px mx-2 bg-[#e8e8e8]"/>
      </template>
    </div>
  </section>
</template>
