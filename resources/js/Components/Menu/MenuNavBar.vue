<script setup lang="ts">
  import {DishMenu} from "@/api";
  import {Ellipsis} from "lucide-vue-next";
  import {ref, watch, PropType, nextTick} from "vue";

  const emits = defineEmits(['switch-menu', 'open-drawer']);

  const props = defineProps({
    menus: {
      type: Array as PropType<DishMenu[]>,
      required: true,
    },
    selected: {
      type: Object as PropType<DishMenu | null>,
      default: null,
    },
    navigation: {
      type: Boolean,
      default: true,
    }
  });

  const scrollRef = ref<HTMLElement | null>(null);

  // Keep the selected menu in view
  watch(() => props.selected, async (newMenu, oldMenu) => {
    if (newMenu === oldMenu) {
      return;
    }

    await nextTick();

    const scroll = scrollRef.value;
    const button = newMenu ? document.getElementById(`menu-${newMenu.id}-button`) : null;

    scroll?.scrollTo({
      top: 0,
      left: button ? Math.max(0, button.offsetLeft - 8) : 0,
      behavior: 'smooth',
    });
  }, {immediate: true});
</script>

<template>
  <div class="w-full flex flex-col justify-center"
       v-if="menus && menus.length">
    <div class="w-full flex justify-between items-start overflow-x-hidden">
      <div class="max-w-full flex justify-start items-start gap-3 pl-2 pr-4 pt-1 pb-0 transition-all duration-200 overflow-x-auto overflow-y-hidden no-scrollbar"
           ref="scrollRef"
           style="scrollbar-gutter: stable;">
        <template v-for="m in menus" :key="m.id">
          <h2 class="font-bold text-lg normal-case py-1.5 pt-1 px-1 whitespace-nowrap cursor-pointer"
              :class="{'opacity-50': selected?.id !== m.id}"
                  :id="`menu-${m.id}-button`"
                  @click="emits('switch-menu', m)">
            {{ m.title }}
          </h2>
        </template>
      </div>

      <div class="w-fit pl-0 p-2 pt-1.5 pb-1 bg-base-100"
           v-if="navigation"
           @click="emits('open-drawer')">
        <div class="btn btn-sm flex justify-center items-center normal-case rounded bg-base-100">
          <Ellipsis class="w-5 h-5 text-base-content/80"/>
        </div>
      </div>
    </div>
  </div>
</template>
