<script setup lang="ts">
  import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";
  import { computed, ref, watch, PropType, nextTick } from "vue";
  import { Restaurant, DishCategory, DishMenu, Dish } from "@/api";
  import SearchWithList from "@/Components/Drawer/SearchWithList.vue";
  import LanguageButton from "@/Components/Base/LanguageButton.vue";

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
      type: Array as PropType<DishMenu[] | null>,
      required: false,
      default: null,
    },
    products:{
      type: Array as PropType<Dish[] | null>,
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

  const emits = defineEmits(['close', 'open-menu', 'open-category', 'open-product', 'open-language', 'query-updated']);

  const searchInputRef = ref<HTMLInputElement | null>(null);
  const hasResults = ref(false);
  const searchQuery = ref("");
  // A filter is selected, or the filters are open
  const filtering = ref(false);

  // The menus and their categories are listed until something is searched for
  const browsing = computed(() => !hasResults.value && !searchQuery.value?.length && !filtering.value);

  function close() {
    searchQuery.value = "";
    filtering.value = false;
    hasResults.value = false;
    emits('close');
  }

  function setHasResults(has: boolean) {
    hasResults.value = has;
  }

  function openMenu(menu: DishMenu) {
    emits('open-menu', menu);
    close();
  }

  function openCategory(category: DishCategory, menu: DishMenu) {
    emits('open-category', category, menu);
    close();
  }

  // The dish's page opens over the search, which stays as it is (its query, filters and scroll)
  function openProduct(product: Dish, category: DishCategory, menu: DishMenu) {
    emits('open-product', product, category, menu);
  }

  function onQueryUpdated(query: string) {
    searchQuery.value = query;
    emits('query-updated', query);
  }

  function onFilteringChanged(val: boolean) {
    filtering.value = val;
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
    <template #actions>
      <LanguageButton @click="emits('open-language')"/>
    </template>

    <div class="w-full h-full flex flex-col">
      <SearchWithList class="min-h-0"
                      :class="{'flex-1': !browsing}"
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
                      @query-updated="onQueryUpdated"
                      @filtering-changed="onFilteringChanged"/>

      <!-- The padding is inside, so it doesn't make the list taller than the space left for it -->
      <div class="w-full flex-1 min-h-0 overflow-auto"
           v-if="browsing">
        <div class="w-full flex flex-col pt-1 px-3 pb-[250px]">
          <template v-for="menu in menus" :key="menu.id">
            <div class="w-full flex flex-col text-start p-3 cursor-pointer"
                 @click="openMenu(menu)">
              <h3 class="text-xl/7 font-bold">
                {{ menu.title }}
              </h3>
              <p class="text-[15px]/[22px] text-base-content/65"
                 v-if="menu.description?.length">
                {{ menu.description }}
              </p>
            </div>

            <div class="w-full flex flex-col pl-5">
              <template v-for="category in menu.categories" :key="category.id">
                <div class="w-full min-h-12 flex flex-col justify-center text-start py-2.5 px-3 cursor-pointer"
                     @click="openCategory(category, menu)">
                  <h4 class="text-[17px]/[26px] font-semibold">
                    {{ category.title }}
                  </h4>
                  <p class="text-[15px]/[22px] text-base-content/65"
                     v-if="category!.description?.length">
                    {{ category.description }}
                  </p>
                </div>
              </template>
            </div>

            <div class="w-full h-px shrink-0 bg-base-300"/>
          </template>
        </div>
      </div>
    </div>
  </BaseDrawer>
</template>
