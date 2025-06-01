<script setup lang="ts">
  import {Category} from "@/api";
  import {AlignRight} from "lucide-vue-next";
  import {ref, watch, PropType, nextTick, onMounted} from "vue";

  const emits = defineEmits(['switch-category', 'open-drawer']);

  const props = defineProps({
    categories: {
      type: Array as PropType<Category[]>,
      required: true,
    },
    selected: {
      type: Object as PropType<Category | null>,
      default: null,
    },
    navigation: {
      type: Boolean,
      default: false,
    }
  });

  watch(() => props.selected, (newCategory, oldCategory) => {
    if (newCategory === oldCategory) {
      return;
    }

    const scroll = document.getElementById('category-buttons-scroll');

    if (!newCategory) {
      if (scroll) {
        scroll.scrollTo({
          top: 0,
          left: 0,
          behavior: 'smooth',
        });
      }

      return;
    }

    const button = document.getElementById(`category-${newCategory.id}-button`);

    if (button && scroll) {
      scroll.scrollTo({
        top: 0,
        left: Math.max(0, button.offsetLeft - 8),
        behavior: 'smooth',
      });
    }
  });

  const RIGHT_DIV_WIDTH = 56; // px (adjust if your right fixed div is wider)

  const scrollRef = ref<HTMLElement|null>(null)
  const hasOverflow = ref(false)

  function checkOverflow() {
    if (scrollRef.value) {
      hasOverflow.value = scrollRef.value.scrollWidth > scrollRef.value.clientWidth + 1;
    }
  }

  watch(() => props.categories, () => {
    nextTick(checkOverflow);
  });

  onMounted(() => {
    checkOverflow();
    window.addEventListener('resize', checkOverflow);
  });

  watch(() => props.selected, async (newCategory, oldCategory) => {
    if (newCategory === oldCategory) {
      return;
    }

    await nextTick();
    checkOverflow();

    const scroll = scrollRef.value;
    if (!newCategory) {
      if (scroll) {
        scroll.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
      }
      return;
    }
    const button = document.getElementById(`category-${newCategory.id}-button`);
    if (button && scroll) {
      scroll.scrollTo({
        top: 0,
        left: Math.max(0, button.offsetLeft - 8),
        behavior: 'smooth',
      });
    }
  }, {immediate: true});

</script>

<template>
  <div class="w-full flex flex-col justify-center"
       v-if="categories && categories.length">
    <div class="w-full flex justify-center items-start">
      <div
        ref="scrollRef"
        :class="[
          'max-w-full flex justify-start items-start gap-2 p-2 pt-0 pb-2 transition-all duration-200 overflow-x-auto overflow-y-hidden no-scrollbar',
          navigation && hasOverflow ? 'pr-16' : ''
        ]"
        id="category-buttons-scroll"
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

      <div class="w-fit p-2 bg-base-100 absolute right-0"
           v-if="navigation"
           @click="$emit('open-drawer')">
        <div class="btn btn-outline h-[32px] p-2 flex justify-center items-center normal-case rounded">
          <AlignRight class="w-6 h-5"/>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
  .no-scrollbar {
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
    scrollbar-width: none;  /* Firefox */
  }

  .no-scrollbar::-webkit-scrollbar {
    display: none;  /* Safari and Chrome */
  }
</style>
