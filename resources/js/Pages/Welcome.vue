<script setup lang="ts">
  import {ref} from "vue";
  // Just to check if TypeScript works
  const title = ref("UI with Inertia.js");
</script>


<template>
  <div class="w-full h-full min-h-screen flex flex-col justify-center items-center">
    <div class="max-w-sm mb-10">
      <h1 class="text-xl font-light underline text-center font-mono">
        {{ title }}
      </h1>
    </div>
  </div>
</template>
