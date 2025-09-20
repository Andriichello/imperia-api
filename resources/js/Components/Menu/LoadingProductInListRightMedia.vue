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
    <div class="card-body px-2 pb-3 py-3 min-h-[100px] rounded text-start relative">
      <div class="flex justify-between items-start gap-1">
        <!-- Left: title + description skeletons -->
        <div class="flex flex-col justify-start items-start gap-2">
          <div class="flex justify-between items-center">
            <div class="grow flex flex-col justify-center items-start card-title gap-0">
              <h2 class="skeleton min-w-[45%] min-h-[20px] rounded-sm bg-warning/20" />
            </div>
          </div>

          <div class="flex-grow">
            <p class="skeleton min-w-full min-h-[15px] rounded-sm bg-warning/20" />
            <p class="skeleton min-w-[80%] max-w-[90%] min-h-[15px] rounded-sm bg-warning/20 mt-1" />
          </div>
        </div>

        <!-- Right: media placeholder (matches ProductInListRightMedia proportions) -->
        <div class="w-40 h-40 rounded relative translate-x-1 -translate-y-1">
          <div class="absolute w-full top-0 h-30 overflow-hidden flex flex-col justify-center rounded border-1 border-warning-content/20">

          </div>
          <div class="w-40 h-30 rounded" />
        </div>
      </div>

      <!-- Badges row: time + calories skeletons -->
      <div class="card-actions justify-between items-end gap-0 mt-2">
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
      <div class="card-actions justify-between items-end mt-2">
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
