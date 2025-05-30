<script setup lang="ts">
  import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";
  import { ref, watch, PropType, nextTick } from "vue";
  import { Restaurant, Category, Menu, Product } from "@/api";
  import SearchWithList from "@/Components/Drawer/SearchWithList.vue";

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
    products:{
      type: Array as PropType<Product[] | null>,
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
  const searchQuery = ref("");

  function close() {
    searchQuery.value = "";
    hasResults.value = false;
    emits('close');
  }

  function setHasResults(has: boolean) {
    hasResults.value = has;
  }

  function openMenu(menu: Menu) {
    emits('open-menu', menu);
    close();
  }

  function openCategory(category: Category, menu: Menu) {
    emits('open-category', category, menu);
    close();
  }

  function openProduct(product: Product, category: Category, menu: Menu) {
    emits('open-product', product, category, menu);
    close();
  }

  function onQueryUpdated(query: string) {
    searchQuery.value = query;
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

    <div class="w-full h-full flex flex-col">
      <SearchWithList class="max-h-full"
                      :open="open"
                      :restaurant="restaurant"
                      :menus="menus"
                      :products="products"
                      :loading="loading"
                      :withPlaceholders="false"
                      @close="close"
                      @has-results-changed="setHasResults"
                      @open-menu="openMenu"
                      @open-category="openCategory"
                      @open-product="openProduct"
                      @query-updated="onQueryUpdated"/>

      <div class="w-full flex flex-col px-6 pb-[250px] overflow-auto"
           v-if="!hasResults && !searchQuery?.length">
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
    </div>
  </BaseDrawer>
</template>
