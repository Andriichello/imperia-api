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
  <section class="w-full flex flex-col px-2 mt-4"
           :id="'category-' + category.id">
    <div class="w-full flex flex-col text-center py-2.5 px-3 bg-primary/20 border border-primary/60 rounded-t-xl cursor-pointer"
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
        <ProductInListRightMedia class="cursor-pointer"
                       :product="product"
                       :preview="true"
                       :currency="currency"
                       :establishment="establishment"
                       @product-click="onProductClick"/>

        <div class="h-px mx-2 bg-[#e8e8e8]"/>
      </template>
    </div>
  </section>
</template>
