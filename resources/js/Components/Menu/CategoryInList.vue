<script setup lang="ts">
  import {Category, Menu, Product} from "@/api";
  import {Splide, SplideSlide} from "@splidejs/vue-splide";
  import ProductInList from "@/Components/Menu/ProductInList.vue";

  const emits = defineEmits(['switch-category']);

  const props = defineProps({
    category: Object as PropType<Category>,
    products: Array as PropType<Product[]>,
  });
</script>

<template>
  <div class="w-full flex flex-col">
    <div class="w-full flex flex-col text-center pt-4 pb-2"
         @click="emits('switch-category', category)"
         :id="'category-' + category.id">

      <h3 class="text-xl">
        {{ category.title }}
      </h3>
      <p class="text-md font-light opacity-80"
         v-if="category!.description?.length">
        {{ category.description }}
      </p>
    </div>

<!--    <div class="w-full h-[1px] bg-base-300"/>-->

    <template v-if="!products!.length">
      <div class="w-full flex flex-col text-center p-2">
        <h3 class="text-md text-light">Unfortunately, this category is empty</h3>
      </div>
    </template>

    <div class="w-full flex flex-col px-2 py-2 gap-3">
      <template v-for="product in products" :key="product.id" v-else>
        <ProductInList :product="product"/>
      </template>
    </div>
  </div>
</template>
