<script setup lang="ts">
  import {ChevronLeft, Search, Languages} from "lucide-vue-next";

  const emits = defineEmits(['on-back', 'on-search', 'on-language']);
</script>

<template>
  <div class="w-full flex justify-between gap-2">
    <button class="btn btn-sm bg-base-100" @click="emits('on-back')">
      <ChevronLeft class="w-5 h-5 text-base-content/70"/>
    </button>

    <div class="flex gap-2">
      <button class="btn btn-sm bg-base-100" @click="emits('on-language')">
        <Languages class="w-5 h-5 text-base-content/80"/>
      </button>

      <button class="btn btn-sm bg-base-100" @click="emits('on-search')">
        <Search class="w-5 h-5 text-base-content/80"/>
      </button>
    </div>
  </div>
</template>
