<script setup lang="ts">
  import {Info} from 'lucide-vue-next'
</script>

<template>
  <div class="flex gap-2.5 p-3 rounded-lg bg-zinc-100 text-[13px]/[18px] text-zinc-700">
    <Info class="size-4 shrink-0 mt-px text-zinc-500"/>

    <p><slot/></p>
  </div>
</template>
