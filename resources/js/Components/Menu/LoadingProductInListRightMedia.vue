<script setup lang="ts">
import { Timer, Flame } from "lucide-vue-next";
</script>

<template>
  <div class="w-full flex flex-col gap-2.5 px-2 py-3.5">
    <div class="w-full flex items-stretch gap-3">
      <div class="flex-1 min-w-0 flex flex-col gap-1.5">
        <h3 class="skeleton w-[60%] h-[22px] my-0.5 bg-base-content/10 rounded-sm"></h3>

        <div class="w-full flex flex-col gap-1.5">
          <p class="skeleton w-[95%] h-[16px] bg-base-content/10 rounded-sm"></p>
          <p class="skeleton w-[95%] h-[16px] bg-base-content/10 rounded-sm"></p>
          <p class="skeleton w-[75%] h-[16px] bg-base-content/10 rounded-sm"></p>
        </div>

        <!-- Time and calories, at the bottom of the text (matches ProductInListRightMedia) -->
        <div class="mt-auto pt-0.5 flex flex-wrap items-center gap-x-3 text-[13px]/5 text-base-content/25">
          <div class="flex items-center gap-1">
            <Timer class="size-3.5"/>
            <p class="skeleton w-[45px] h-[14px] rounded-sm bg-base-content/10"/>
          </div>

          <div class="flex items-center gap-1">
            <Flame class="size-3.5"/>
            <p class="skeleton w-[50px] h-[14px] rounded-sm bg-base-content/10"/>
          </div>
        </div>
      </div>

      <!-- Photo placeholder (matches ProductInListRightMedia) -->
      <div class="size-28 shrink-0 self-start rounded-lg border border-base-300"/>
    </div>

    <!-- Sizes and price -->
    <div class="flex justify-between items-end gap-2">
      <div class="flex gap-1.5">
        <p class="skeleton w-[70px] h-10 rounded bg-primary/20"/>
        <p class="skeleton w-[70px] h-10 rounded bg-primary/20"/>
      </div>

      <p class="skeleton w-[80px] h-7 mb-1.5 rounded-sm bg-base-content/10"/>
    </div>
  </div>
</template>
