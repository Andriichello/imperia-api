<script setup lang="ts">
  import {Category, Menu, Product} from "@/api";
  import CategoryInList from "@/Components/Menu/CategoryInList.vue";
  import {PropType} from "vue";
  import {Expand} from "lucide-vue-next";
  import ProductInList from "@/Components/Menu/ProductInList.vue";
  import LoadingProductInList from "@/Components/Menu/LoadingProductInList.vue";
  import LoadingCategoryInList from "@/Components/Menu/LoadingCategoryInList.vue";

  const emits = defineEmits(['switch-menu', 'switch-category']);

  const props = defineProps({
    products: {
      type: Array as PropType<Product[]>,
      required: false,
      default: [{image: true}, {image: false}],
    },
    establishment: {
      type: String as PropType<string | null>,
      default: 'restaurant',
    },
    currency: {
      type: String as PropType<string | null>,
      required: false,
      default: 'uah',
    },
  });
</script>

<template>
  <div class="w-full flex flex-col">
    <p class="self-center skeleton w-[40%] h-[16px] bg-base-content/10 rounded-sm mt-4"/>

    <div class="w-full flex flex-col">
      <div class="w-full flex flex-col gap-3">
        <LoadingCategoryInList :products="[{image: true}, {image: false}]"
                               :image="true"
                               :currency="currency ?? 'uah'"
                               :establishment="establishment"/>
      </div>
    </div>
  </div>
</template>
