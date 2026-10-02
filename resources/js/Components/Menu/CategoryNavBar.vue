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
  <div class="w-full flex gap-2 pt-1.5 px-2 pb-2.5 overflow-x-auto overflow-y-hidden no-scrollbar"
       ref="scrollRef"
       v-if="categories && categories.length">
    <button type="button"
            class="h-9 shrink-0 inline-flex items-center px-3.5 rounded-lg border text-[15px] font-semibold whitespace-nowrap cursor-pointer"
            :class="selected?.id === c.id
              ? 'border-primary/40 bg-primary/20 text-primary-content'
              : 'border-zinc-200 bg-base-100 text-base-content'"
            :aria-current="selected?.id === c.id ? 'true' : undefined"
            :id="`category-${c.id}-button`"
            v-for="c in categories" :key="c.id"
            @click="emits('switch-category', c)">
      {{ c.title }}
    </button>
  </div>
</template>
