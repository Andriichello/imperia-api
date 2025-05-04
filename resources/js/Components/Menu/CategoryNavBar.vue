<script setup lang="ts">
  import {Category} from "@/api";
  import {AlignRight} from "lucide-vue-next";
  import {watch, PropType} from "vue";

  const emits = defineEmits(['switch-category', 'open-drawer']);

  const props = defineProps({
    categories: {
      type: Array as PropType<Category[]>,
      required: true,
    },
    selected: {
      type: Object as PropType<Category | null>,
      required: true,
    },
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
</script>

<template>
  <div class="w-full flex flex-col justify-center shadow-md mb-2"
       v-if="categories && categories.length">
    <div class="w-full flex justify-center items-start">
      <div class="max-w-full flex justify-start items-start gap-2 p-2 overflow-x-auto overflow-y-hidden"
           id="category-buttons-scroll">
        <template v-for="c in categories" :key="c.id">
          <button class="btn btn-sm text-[14px] normal-case"
                  :id="`category-${c.id}-button`"
                  :class="{'btn-ghost':  selected?.id !== c.id, 'btn-neutral': selected?.id === c.id}"
                  @click="emits('switch-category', c)">
            {{ c.title }}
          </button>
        </template>
      </div>

      <div class="w-fit h-full bg-base-100 sticky right-0 p-2"
           v-if="false"
           @click="$emit('open-drawer')">
        <div class="btn btn-outline h-[32px] p-2 flex justify-center items-center normal-case rounded">
          <AlignRight class="w-6 h-5"/>
        </div>
      </div>
    </div>
  </div>
</template>
