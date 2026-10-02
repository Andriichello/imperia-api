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
  <div class="w-full flex flex-col px-2 mt-4">
    <div class="skeleton w-full flex flex-col py-2.5 px-3 bg-primary/15 border border-primary/40 rounded-b-none rounded-t-xl">
      <h3 class="h-[30px]"/>
    </div>

    <div class="w-full flex flex-col">
      <template v-for="n in count" :key="n">
        <LoadingProductInListRightMedia/>

        <div class="h-px mx-2 bg-[#e8e8e8]"/>
      </template>
    </div>
  </div>
</template>
