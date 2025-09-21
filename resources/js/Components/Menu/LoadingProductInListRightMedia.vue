<script setup lang="ts">
import { PropType } from "vue";
import DiagonalPattern from "@/Components/Base/DiagonalPattern.vue";
import { Timer, Flame } from "lucide-vue-next";

const props = defineProps({
  establishment: {
    type: String as PropType<string | null>,
    default: "restaurant",
  },
});
</script>

<template>
  <div class="w-full flex flex-col rounded">
    <div class="w-full card-body px-2 pb-3 py-3 rounded text-start relative">
      <div class="w-full flex justify-between items-start gap-1">
        <div class="w-full flex flex-col justify-start gap-2">
          <h2 class="skeleton w-[60%] h-[24px] mt-1 bg-base-content/10 rounded-sm"></h2>

          <div class="w-full flex flex-col justify-start gap-1">
            <p class="skeleton w-[95%] h-[16px] bg-base-content/10 rounded-sm"></p>
            <p class="skeleton w-[95%] h-[16px] bg-base-content/10 rounded-sm"></p>
            <p class="skeleton w-[75%] h-[16px] bg-base-content/10 rounded-sm"></p>
          </div>
        </div>

        <!-- Right: media placeholder (matches ProductInListRightMedia proportions) -->
        <div class="w-40 h-30 rounded relative translate-x-1 -translate-y-1">
          <div class="absolute w-full top-0 h-30 overflow-hidden flex flex-col justify-center rounded border-1 border-warning-content/20">

          </div>
          <div class="w-40 h-30 rounded" />
        </div>
      </div>

      <!-- Badges row: time + calories skeletons -->
      <div class="card-actions justify-between items-end gap-0">
        <div class="w-full flex flex-wrap gap-x-3 gap-y-0.5 normal-case text-[12px] text-base-content/25">
          <div class="flex flex-row justify-center items-center gap-1">
            <Timer class="w-4 h-4" />
            <p class="skeleton w-[45px] max-w-[45px] h-[14px] rounded-sm mt-0.5 bg-base-content/10" />
          </div>

          <div class="flex flex-row justify-center items-center gap-1">
            <Flame class="w-4 h-4" />
            <p class="skeleton w-[50px] max-w-[50px] h-[14px] rounded-sm mt-0.5 bg-base-content/10" />
          </div>
        </div>
      </div>

      <!-- Bottom actions: variant pills + price skeletons -->
      <div class="card-actions justify-between items-end">
        <div class="flex gap-1">
          <p class="skeleton max-w-[70px] w-[70px] h-[24px] rounded-sm bg-warning/20" />
          <p class="skeleton max-w-[70px] w-[70px] h-[24px] rounded-sm bg-warning/20" />
        </div>

        <div class="p-0 justify-end">
          <p class="skeleton max-w-[80px] w-[80px] h-[28px] rounded-sm bg-warning/20" />
        </div>
      </div>
    </div>
  </div>
</template>
