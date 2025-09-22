<script setup lang="ts">
  import {useAppStore} from "@/stores/app";

  const app = useAppStore();
  console.log('locale: ', app.locale);

  // if needed, perform locale and/or theme setup here
</script>

<template>
  <router-view />
</template>
