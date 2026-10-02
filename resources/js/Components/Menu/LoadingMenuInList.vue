<script setup lang="ts">
  import LoadingCategoryInList from "@/Components/Menu/LoadingCategoryInList.vue";
</script>

<template>
  <div class="w-full flex flex-col">
    <p class="self-center skeleton w-[40%] h-[16px] bg-base-content/10 rounded-sm mt-4"/>

    <div class="w-full flex flex-col">
      <div class="w-full flex flex-col gap-3">
        <LoadingCategoryInList/>
      </div>
    </div>
  </div>
</template>
