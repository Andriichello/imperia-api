<script setup lang="ts">
  import {DishCategory} from "@/api";
  import {ref, watch, PropType, nextTick} from "vue";

  const emits = defineEmits(['switch-category']);

  const props = defineProps({
    categories: {
      type: Array as PropType<DishCategory[]>,
      required: true,
    },
    selected: {
      type: Object as PropType<DishCategory | null>,
      default: null,
    },
  });

  const scrollRef = ref<HTMLElement | null>(null);

  // Keep the selected category in view
  watch(() => props.selected, async (newCategory, oldCategory) => {
    if (newCategory === oldCategory) {
      return;
    }

    await nextTick();

    const scroll = scrollRef.value;
    const button = newCategory ? document.getElementById(`category-${newCategory.id}-button`) : null;

    scroll?.scrollTo({
      top: 0,
      left: button ? Math.max(0, button.offsetLeft - 8) : 0,
      behavior: 'smooth',
    });
  }, {immediate: true});
</script>

<template>
  <div class="w-full flex flex-col justify-center"
       v-if="categories && categories.length">
    <div class="w-full flex justify-start items-start">
      <div class="max-w-full flex justify-start items-start gap-2 p-2 pt-1 pb-2 transition-all duration-200 overflow-x-auto overflow-y-hidden no-scrollbar"
           ref="scrollRef"
           style="scrollbar-gutter: stable;">
        <template v-for="c in categories" :key="c.id">
          <button class="btn btn-sm text-[14px] normal-case"
                  :id="`category-${c.id}-button`"
                  :class="{'btn-ghost':  selected?.id !== c.id, 'btn-warning bg-warning/20 border-warning/40': selected?.id === c.id}"
                  @click="emits('switch-category', c)">
            {{ c.title }}
          </button>
        </template>
      </div>
    </div>
  </div>
</template>
