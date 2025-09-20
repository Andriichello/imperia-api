<script setup lang="ts">
  import {Dish, DishCategory} from "@/api";
  import {PropType} from "vue";
  import {useI18n} from "vue-i18n";
  import ProductInListRightMedia from "@/Components/Menu/ProductInListRightMedia.vue";

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
    establishment: {
      type: String as PropType<string | null>,
      default: 'restaurant',
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
  });

  const i18n = useI18n();

  function onProductClick(product: Dish) {
    emits('open-product', { product, category: props.category, menu: props.category.menu });
  }
</script>

<template>
  <div class="w-full flex flex-col px-2"
       :id="'category-' + category.id">
    <div class="w-full flex flex-col text-center p-2 bg-warning/20 border-1 border-warning/60 text-warning-content rounded-t-xl mt-4 cursor-pointer"
         @click="emits('switch-category', category)">
      <h3 class="text-xl">
        {{ category.title }}
      </h3>
      <p class="text-md font-light opacity-80"
         v-if="category.description?.length">
        {{ category.description }}
      </p>
    </div>

    <template v-if="!products!.length">
      <div class="w-full flex flex-col text-center p-2">
        <h3 class="text-md text-light">{{ i18n.t('menu.empty_category') }}</h3>
      </div>
    </template>

    <div class="w-full flex flex-col py-2 gap-2"
         :id="'category-' + category.id + '-products'"
         v-else>
      <template v-for="product in products" :key="product.id">
        <ProductInListRightMedia class="cursor-pointer"
                       :product="product"
                       :preview="true"
                       :currency="currency"
                       :establishment="establishment"
                       @product-click="onProductClick"/>

        <div class="w-full h-[1px] flex flex-col bg-warning-content/25"/>
      </template>
    </div>
  </div>
</template>
