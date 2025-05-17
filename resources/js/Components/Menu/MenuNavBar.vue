<script setup lang="ts">
import {Menu} from "@/api";
  import {AlignRight} from "lucide-vue-next";
  import {ref, watch, PropType, nextTick, onMounted} from "vue";

  const emits = defineEmits(['switch-menu', 'open-drawer']);

  const props = defineProps({
    menus: {
      type: Array as PropType<Menu[]>,
      required: true,
    },
    selected: {
      type: Object as PropType<Menu | null>,
      default: null,
    },
    navigation: {
      type: Boolean,
      default: true,
    }
  });

  watch(() => props.selected, (newMenu, oldMenu) => {
    if (newMenu === oldMenu) {
      return;
    }

    const scroll = document.getElementById('menu-buttons-scroll');

    if (!newMenu) {
      if (scroll) {
        scroll.scrollTo({
          top: 0,
          left: 0,
          behavior: 'smooth',
        });
      }

      return;
    }

    const button = document.getElementById(`menu-${newMenu.id}-button`);

    if (button && scroll) {
      scroll.scrollTo({
        top: 0,
        left: Math.max(0, button.offsetLeft - 8),
        behavior: 'smooth',
      });
    }
  });

  const RIGHT_DIV_WIDTH = 56; // px (adjust if your right fixed div is wider)

  const scrollRef = ref<HTMLElement | null>(null);
  const menuNavbarRef = ref<HTMLElement | null>(null);

  const hasOverflow = ref(false);

  function checkOverflow() {
    if (scrollRef.value && menuNavbarRef.value) {
      hasOverflow.value = scrollRef.value.scrollWidth > menuNavbarRef.value.clientWidth + 1;
    }
  }

  watch(() => props.menus, () => {
    nextTick(checkOverflow);
  });

  onMounted(() => {
    checkOverflow();
    window.addEventListener('resize', checkOverflow);
  });

  watch(() => props.selected, async (newMenu, oldMenu) => {
    if (newMenu === oldMenu) return;
    await nextTick();
    checkOverflow();

    const scroll = scrollRef.value;
    if (!newMenu) {
      if (scroll) {
        scroll.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
      }
      return;
    }
    const button = document.getElementById(`menu-${newMenu.id}-button`);
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
  <div class="w-full flex flex-col justify-center"
       ref="menuNavbarRef"
       v-if="menus && menus.length">
    <div class="w-full flex justify-center items-start">
      <div
        ref="scrollRef"
        :class="[
          'max-w-full flex justify-start items-start gap-2 p-2 pb-1 transition-all duration-200 overflow-x-auto overflow-y-hidden',
          navigation && hasOverflow ? 'pr-16' : ''
        ]"
        id="menu-buttons-scroll"
        style="scrollbar-gutter: stable;">
        <template v-for="m in menus" :key="m.id">
          <h2 class="font-bold text-lg normal-case py-1.5 pt-1 px-1 whitespace-nowrap"
              :class="{'opacity-50': selected.id !== m.id}"
                  :id="`menu-${m.id}-button`"
                  @click="emits('switch-menu', m)">
            {{ m.title }}
          </h2>
        </template>
      </div>

      <div class="w-fit pl-0 p-2 bg-base-100 absolute right-0"
           v-if="navigation"
           @click="$emit('open-drawer')">
        <div class="btn btn-outline h-[32px] p-2 flex justify-center items-center normal-case rounded bg-base-100">
          <AlignRight class="w-6 h-5"/>
        </div>
      </div>
    </div>
  </div>
</template>
