<script setup lang="ts">
  import {Coffee, Pizza, CakeSlice, Utensils, Croissant, ChefHat} from "lucide-vue-next";
  import {computed, PropType} from "vue";
  import {Restaurant} from "@/api";

  const props = defineProps({
    establishment: {
      type: String as PropType<string>,
      required: false,
      default: 'restaurant',
    },
    rows: {
      type: Number as PropType<number>,
      default: 10,
    },
    cols: {
      type: Number as PropType<number>,
      default: 10,
    }
  });

  const isCafe = computed(
    () => props.establishment.includes('café') || props.establishment.includes('cafe')
  );

  const isPizzeria = computed(
    () => props.establishment.includes('pizzeria')
  );

  const isBakery = computed(
    () => props.establishment.includes('bakery')
  );
</script>

<template>
  <div class="flex flex-col justify-between gap-6 -rotate-45">
    <template v-for="j in rows">
      <div class="w-full flex justify-between gap-1">
        <Coffee class="w-2 h-2" v-for="u in cols" :key="'row-' + j + 'coffee'+u"
                v-if="isCafe"/>

        <Pizza class="w-2 h-2 rotate-45" v-for="u in cols" :key="'row-' + j + 'pizza'+u"
               v-else-if="isPizzeria"/>

        <CakeSlice class="w-2 h-2 rotate-45" v-for="u in cols" :key="'row-' + j + 'bakery'+u"
                   v-else-if="isBakery"/>

        <Utensils class="w-2 h-2" v-for="u in cols" :key="'row-' + j + 'utensils'+u"
                  v-else/>
      </div>

      <div class="w-full flex justify-between gap-1">
        <Croissant class="w-2 h-2 -rotate-45" v-for="u in cols" :key="'row-' + j + 'croissant'+u"
                   v-if="isCafe || isBakery"/>

        <ChefHat class="w-2 h-2" v-for="c in cols" :key="'row-' + j + 'chef'+c"
                 v-else/>
      </div>
    </template>
  </div>
</template>
