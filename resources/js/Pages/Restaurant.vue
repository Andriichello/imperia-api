<script setup lang="ts">
  const props = defineProps({
    restaurant: Object,
  });

  console.log(props.restaurant);

  // Component logic goes here...
</script>

<template>
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-center items-center">
    <div class="max-w-sm mb-10">
      <h1 class="text-xl font-light underline text-center font-mono">
        Restaurant Page
      </h1>
    </div>
  </div>
</template>
