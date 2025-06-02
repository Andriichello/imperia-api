<script setup lang="ts">
  import {PropType} from "vue";
  import {priceFormatted, weightUnitFormatted} from "@/helpers";
  import DiagonalPattern from "@/Components/Base/DiagonalPattern.vue";
  import {Timer, Flame, Vegan, Leaf, Nut, EggFried, Salad} from "lucide-vue-next";
  import { useI18n } from "vue-i18n";

  const i18n = useI18n();

  const props = defineProps({
    currency: {
      type: String as PropType<string | null>,
      required: false,
      default: null,
    },
    establishment: {
      type: String as PropType<string | null>,
      default: 'restaurant',
    },
    image: {
      type: Boolean as PropType<boolean>,
      default: false,
    }
  });
</script>

<template>
  <div class="w-full flex flex-col rounded shadow-md bg-base-100">
    <template v-if="image">
      <div class="w-full h-45 rounded-t relative">
        <div class="absolute w-full top-0 h-45 border-b-1 border-base-300 overflow-hidden flex flex-col justify-center rounded-t">
          <DiagonalPattern class="scale-165 text-warning-content/30"
                           :establishment="establishment ?? 'restaurant'"/>
        </div>
      </div>
    </template>

    <div class="card-body min-h-[100px] rounded text-start relative">
      <div class="flex justify-between items-center">
        <div class="grow flex flex-col justify-center items-start card-title gap-0">
          <h2 class="skeleton min-w-[45%] min-h-[20px] rounded-sm bg-warning/20"/>
        </div>
      </div>

      <div class="flex flex-col gap-1">
        <p class="skeleton min-w-full min-h-[15px] rounded-sm bg-warning/20"/>
        <p class="skeleton min-w-[80%] max-w-[90%] min-h-[15px] rounded-sm bg-warning/20"/>
      </div>

      <div class="card-actions justify-between items-end">
        <div class="flex flex-wrap gap-x-3 gap-y-0.5 normal-case text-[12px] text-base-content/25">
          <div class="flex flex-row justify-center items-center gap-1">
            <Timer class="w-4 h-4"/>
            <p class="skeleton w-[45px] max-w-[45px] h-[14px] rounded-sm mt-0.5 bg-base-content/10"/>
          </div>

          <div class="flex flex-row justify-center items-center gap-1">
            <Flame class="w-4 h-4"/>
            <p class="skeleton w-[50px] max-w-[50px] h-[14px] rounded-sm mt-0.5 bg-base-content/10"/>
          </div>
        </div>
      </div>

      <div class="w-full h-[28px] flex gap-3 justify-between items-end">
        <p class="skeleton max-w-[60px] h-[24px] rounded-sm bg-warning/20"/>
        <p class="skeleton max-w-[80px] h-[28px] rounded-sm bg-warning/20"/>
      </div>
    </div>
  </div>
</template>
