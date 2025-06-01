<script setup lang="ts">
  import {Category, Product} from "@/api";
  import ProductInList from "@/Components/Menu/ProductInList.vue";
  import {PropType} from "vue";
  import {useI18n} from "vue-i18n";

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
    establishment: {
      type: String as PropType<string | null>,
      default: 'restaurant',
    },
    preview: {
      type: Boolean as PropType<boolean>,
      default: false,
    }
  });

  const i18n = useI18n();
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

    <div class="w-full flex flex-col py-2 gap-3"
         :id="'category-' + category.id + '-products'"
         v-else>
      <template v-for="product in products" :key="product.id">
        <ProductInList class="border-1 border-warning-content/20"
                       :product="product"
                       :preview="preview"
                       :establishment="establishment"/>
      </template>
    </div>
  </div>
</template>
