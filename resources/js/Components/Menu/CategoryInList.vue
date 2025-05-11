<script setup lang="ts">
  import {Category, Product} from "@/api";
  import ProductInList from "@/Components/Menu/ProductInList.vue";
  import {PropType} from "vue";

  const emits = defineEmits(['switch-category']);

  const props = defineProps({
    category: {
      type: Object as PropType<Category>,
      required: true,
    },
    products: {
      type: Array as PropType<Product[]>,
      required: true,
    },
  });
</script>

<template>
  <div class="w-full flex flex-col px-2"
       :id="'category-' + category.id">
    <div class="w-full flex flex-col text-center p-2 bg-neutral text-neutral-content rounded-t-xl mt-4 cursor-pointer"
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
        <h3 class="text-md text-light">Unfortunately, this category is empty</h3>
      </div>
    </template>

    <div class="w-full flex flex-col py-2 gap-3" v-else>
      <template v-for="product in products" :key="product.id">
        <ProductInList :product="product"/>
      </template>
    </div>
  </div>
</template>
