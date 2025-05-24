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
  const hasResults = ref(false);

  function close() {
    emits('close');
  }

  function setHasResults(has: boolean) {
    hasResults.value = has;
  }

  function openMenu(menu: Menu) {
    emits('open-menu', menu);
    close();
  }

  function openCategory(category: Category, menu: Menu = null as any) {
    const m = menu ?? props.menus?.filter(menu => menu.categories.includes(category))[0] ?? null;

    if (m) {
      emits('open-category', category, m);
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
                    :withPlaceholders="false"
                    @close="close"
                    @has-results-changed="setHasResults"
                    @open-menu="openMenu"
                    @open-category="openCategory"
                    @open-product="openProduct"
                    @query-updated="onQueryUpdated"/>

    <div class="w-full h-full flex flex-col px-6 overflow-auto"
         v-if="!hasResults">
      <template v-for="menu in menus" :key="menu.id">
        <div class="w-full flex flex-col text-start py-3 px-3 cursor-pointer"
             @click="openMenu(menu)">
          <h3 class="text-xl font-bold">
            {{ menu.title }}
          </h3>
          <p class="text-md font-light opacity-80">
            {{ menu.description?.length ? menu.description : 'menu' }}
          </p>
        </div>

        <div class="w-full h-[1px] bg-base-300"/>

        <div class="w-full flex flex-col pl-5">
          <template v-for="category in menu.categories" :key="category.id">
            <div class="w-full flex flex-col text-start py-3 px-3 cursor-pointer"
                 @click="openCategory(category, menu)">
              <h3 class="text-lg font-bold">
                {{ category.title }}
              </h3>
              <p class="text-md font-light opacity-80"
                 v-if="category!.description?.length">
                {{ category.description }}
              </p>
            </div>
          </template>
        </div>

        <div class="w-full h-[1px] bg-base-300"/>
      </template>
    </div>
  </BaseDrawer>
</template>
