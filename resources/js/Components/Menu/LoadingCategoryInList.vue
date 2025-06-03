<script setup lang="ts">
  import {Category, Product} from "@/api";
  import ProductInList from "@/Components/Menu/ProductInList.vue";
  import {PropType} from "vue";
  import {useI18n} from "vue-i18n";
  import LoadingProductInList from "@/Components/Menu/LoadingProductInList.vue";

  const props = defineProps({
    products: {
      type: Array as PropType<{image: boolean}[]>,
      required: true,
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

  const i18n = useI18n();
</script>

<template>
  <div class="w-full flex flex-col px-2">
    <div class="skeleton w-full flex flex-col text-center p-2 bg-warning/15 border-1 border-warning/40 text-warning-content rounded-b-none rounded-t-xl mt-4 cursor-pointer">
      <h3 class="h-[28px]"/>
    </div>

    <template v-if="!products?.length">
      <div class="w-full flex flex-col py-2 gap-3">
        <LoadingProductInList class="border-1 border-warning-content/20"
                              :image="true"
                              :currency="currency"
                              :establishment="establishment"/>
      </div>
    </template>

    <div class="w-full flex flex-col py-2 gap-3"
         v-else>
      <template v-for="product in products">
        <LoadingProductInList class="border-1 border-warning-content/20"
                              :image="product?.image ?? false"
                              :currency="currency"
                              :establishment="establishment"/>
      </template>
    </div>
  </div>
</template>
