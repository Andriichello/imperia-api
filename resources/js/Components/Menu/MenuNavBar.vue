<script setup lang="ts">
  import {DishMenu} from "@/api";
  import {Ellipsis} from "lucide-vue-next";
  import {ref, watch, PropType, nextTick} from "vue";
  import {useI18n} from "vue-i18n";

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

  const i18n = useI18n();

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
  <div class="w-full flex items-stretch"
       v-if="menus && menus.length">
    <div class="flex-1 min-w-0 flex items-stretch gap-1 pt-1 px-2 overflow-x-auto overflow-y-hidden no-scrollbar"
         ref="scrollRef">
      <button type="button"
              class="h-9 shrink-0 px-1.5 text-lg font-bold whitespace-nowrap cursor-pointer"
              :class="selected?.id === m.id ? 'text-primary-content' : 'text-base-content/65'"
              :aria-current="selected?.id === m.id ? 'true' : undefined"
              :id="`menu-${m.id}-button`"
              v-for="m in menus" :key="m.id"
              @click="emits('switch-menu', m)">
        {{ m.title }}
      </button>
    </div>

    <button type="button"
            class="size-11 shrink-0 self-center -my-0.5 mr-1 flex items-center justify-center text-base-content cursor-pointer"
            :aria-label="i18n.t('nav.browse')"
            v-if="navigation"
            @click="emits('open-drawer')">
      <Ellipsis class="size-6"/>
    </button>
  </div>
</template>
