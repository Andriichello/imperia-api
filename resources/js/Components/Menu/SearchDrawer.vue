<script setup lang="ts">
  import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";
  import {ref, watch, computed, PropType, nextTick, onMounted} from "vue";
  import { Restaurant, Category, Menu, Product } from "@/api";
  import { useI18n } from "vue-i18n";
  import SearchWithList from "@/Components/Menu/SearchWithList.vue";

  const props = defineProps({
    open: {
      type: Boolean,
      required: true,
    },
    restaurant: {
      type: Object as PropType<Restaurant>,
      required: true,
    },
    menus: {
      type: Array as PropType<Menu[] | null>,
      required: false,
      default: null,
    },
    loading: {
      type: Boolean,
      required: false,
      default: false,
    },
    withAutofocus: {
      type: Boolean,
      required: false,
      default: true,
    },
    withPlaceholders: {
      type: Boolean,
      required: false,
      default: true,
    },
  });

  const emits = defineEmits(['close', 'open-menu', 'open-category', 'open-product', 'query-updated']);

  const searchInputRef = ref<HTMLInputElement | null>(null);

  function close() {
    emits('close');
  }

  function openMenu(menu: Menu) {
    emits('open-menu', menu);
    close();
  }

  function openCategory(category: Category) {
    const menu = props.menus?.filter(menu => menu.categories.includes(category))[0] ?? null;

    if (menu) {
      emits('open-category', category, menu);
      close();
    }
  }

  function openProduct(product: Product) {
    const menu = props.menus?.filter(menu => (menu.products ?? []).includes(product))[0] ?? null;
    const category = menu?.categories?.filter(category => (product.category_ids ?? []).includes(category.id))[0] ?? null;

    emits('open-product', product, category, menu);
    close();
  }

  function onQueryUpdated(query: string) {
    emits('query-updated', query);
  }

  watch(() => props.open, (newVal, oldVal) => {
    if (props.withAutofocus && newVal && newVal !== oldVal) {
      nextTick(() => {
        document.getElementById('searchInputRef')?.focus()
      });
    }
  });
</script>

<template>
  <BaseDrawer :open="open"
              @close="close">

    <SearchWithList :open="open"
                    :restaurant="restaurant"
                    :menus="menus"
                    :loading="loading"
                    :withPlaceholders="true"
                    @open-menu="openMenu"
                    @open-category="openCategory"
                    @open-product="openProduct"
                    @query-updated="onQueryUpdated"/>
  </BaseDrawer>
</template>
