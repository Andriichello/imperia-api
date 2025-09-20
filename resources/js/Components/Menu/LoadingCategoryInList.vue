<script setup lang="ts">
import {PropType} from "vue";
import {useI18n} from "vue-i18n";
import LoadingProductInListRightMedia from "@/Components/Menu/LoadingProductInListRightMedia.vue";

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
      <div class="w-full flex flex-col py-2 gap-2">
        <LoadingProductInListRightMedia
                              :image="true"
                              :currency="currency"
                              :establishment="establishment"/>

        <div class="w-full h-[1px] flex flex-col bg-warning-content/25"/>
      </div>
    </template>

    <div class="w-full flex flex-col py-2 gap-2"
         v-else>
      <template v-for="product in products">
        <LoadingProductInListRightMedia
                              :image="product?.image ?? false"
                              :currency="currency"
                              :establishment="establishment"/>

        <div class="w-full h-[1px] flex flex-col bg-warning-content/25"/>
      </template>
    </div>
  </div>
</template>
