<script setup lang="ts">
  import LoadingProductInListRightMedia from "@/Components/Menu/LoadingProductInListRightMedia.vue";

  const props = defineProps({
    // Number of placeholder dishes
    count: {
      type: Number,
      default: 2,
    },
  });
</script>

<template>
  <div class="w-full flex flex-col px-2">
    <div class="skeleton w-full flex flex-col text-center p-2 bg-warning/15 border-1 border-warning/40 text-warning-content rounded-b-none rounded-t-xl mt-4 cursor-pointer">
      <h3 class="h-[28px]"/>
    </div>

    <div class="w-full flex flex-col py-2 gap-2">
      <template v-for="n in count" :key="n">
        <LoadingProductInListRightMedia/>

        <div class="w-full h-[1px] flex flex-col bg-warning-content/25"/>
      </template>
    </div>
  </div>
</template>
