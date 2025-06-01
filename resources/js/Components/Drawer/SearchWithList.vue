<script setup lang="ts">
  import {ref, watch, computed, PropType, nextTick, onUnmounted} from "vue";
  import { Restaurant, Category, Menu, Product } from "@/api";
  import { Search, X } from "lucide-vue-next";
  import { useI18n } from "vue-i18n";
  import ProductInList from "@/Components/Menu/ProductInList.vue";
  import {Deferred} from "@inertiajs/vue3";
  import MenuInList from "@/Components/Menu/MenuInList.vue";

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
    withPlaceholders: {
      type: Boolean,
      required: false,
      default: false,
    },
  });

  const emits = defineEmits(['open-menu', 'open-category', 'open-product', 'query-updated', 'has-results-changed']);

  const i18n = useI18n();

  const currency = computed(() => props.restaurant?.currency ?? 'uah');

  const searchQuery = ref("");

  const filteredMenus = computed<Menu[]>(() => {
    if (!searchQuery.value) {
      return [];
    }

    return props.menus?.filter(menu =>
      menu.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      menu.description?.toLowerCase().includes(searchQuery.value.toLowerCase())
    ) ?? [];
  });

  const filteredCategories = computed<Category[]>(() => {
    if (!searchQuery.value) {
      return [];
    }

    const filtered: Category[] = [];

    props.menus?.forEach(menu => {
      menu.categories.forEach(category => {
        const matches = category.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          (category.description?.length && category.description.toLowerCase().includes(searchQuery.value.toLowerCase()));

        if (matches && !filtered.includes(category)) {
          filtered.push(category);
        }
      });
    });

    return filtered;
  });

  const filteredProducts = computed<Product[]>(() => {
    if (!searchQuery.value) {
      return [];
    }

    const filtered: Product[] = [];

    props.products?.forEach(product => {
      const matches = product.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (product.description?.length && product.description.toLowerCase().includes(searchQuery.value.toLowerCase()));

      if (matches && !filtered.includes(product)) {
        filtered.push(product);
      }
    });

    return filtered;
  });

  const hasResults = computed(() => {
    return filteredMenus.value.length > 0 ||
           filteredCategories.value.length > 0 ||
           filteredProducts.value.length > 0;
  });

  function clearSearch() {
    searchQuery.value = "";
  }

  function openMenu(menu: Menu) {
    emits('open-menu', menu);
  }

  function openCategory(category: Category) {
    const menu: Menu | null = props.menus?.find((menu: Menu) => menu.categories.includes(category));

    if (menu) {
      emits('open-category', category, menu);
    }
  }

  function openProduct(product: Product) {
    const menu = props.menus?.find(
      (m: Menu) => !!m.categories.find((c: Category) => product.category_ids.includes(c.id))
    );
    const category = menu?.categories?.find(
      (c: Category) => product.category_ids.includes(c.id)
    );

    emits('open-product', product, category, menu);
  }

  watch(() => hasResults.value, (newVal, oldVal) => {
    if (newVal !== oldVal) {
      emits('has-results-changed', newVal);
    }
  });

  watch(() => searchQuery.value, (newVal) => {
    emits('query-updated', newVal);
  });

  onUnmounted(() => {
    clearSearch();
  });
</script>

<template>
  <div class="max-w-full w-full flex flex-col">
    <!-- Search input -->
    <div class="relative mb-4 px-6">
      <div class="flex items-center border-b border-base-300 pb-2">
        <Search class="w-5 h-5 text-base-content/60 mr-2" />
        <input
          v-model="searchQuery"
          id="searchInputRef"
          ref="searchInputRef"
          type="text"
          :placeholder="i18n.t('search.placeholder')"
          class="w-full bg-transparent border-none focus:outline-none text-base-content text-lg"
          autocomplete="off"
        />
      </div>
    </div>

    <!-- Menus list -->
    <Deferred data="products">
      <template #fallback>
        <div class="flex justify-center my-2 mb-0 px-9">
          <div class="loading loading-dots loading-lg opacity-60"></div>
        </div>
      </template>

      <!-- Loading indicator -->
      <div v-if="loading" class="flex justify-center my-2 mb-0 px-9">
        <div class="loading loading-dots loading-lg opacity-60"></div>
      </div>

      <!-- Search results -->
      <div v-else-if="searchQuery" class="overflow-auto pt-2 px-6">
        <div v-if="hasResults" class="pb-[250px]">
          <!-- Menus section -->
          <div v-if="filteredMenus.length > 0" class="mb-6">
            <h3 class="font-bold text-lg mb-2">{{ i18n.t('search.menus') }}</h3>
            <div class="space-y-2">
              <div class="p-3 hover:bg-warning/10 border-1 border-warning-content/40 rounded-lg cursor-pointer transition-colors"
                   v-for="menu in filteredMenus"
                   :key="`menu-${menu.id}`"
                   @click="openMenu(menu)">
                <div class="text-lg font-medium">{{ menu.title }}</div>
                <div class="text-md text-base-content/80 line-clamp-1">{{ menu.description }}</div>
              </div>
            </div>
          </div>

          <!-- Categories section -->
          <div v-if="filteredCategories.length > 0" class="mb-6">
            <h3 class="font-bold text-lg mb-2">{{ i18n.t('search.categories') }}</h3>
            <div class="space-y-2">
              <div class="p-3 hover:bg-warning/10 border-1 border-warning-content/40 rounded-lg cursor-pointer transition-colors"
                   v-for="category in filteredCategories"
                   :key="`category-${category.id}`"
                   @click="openCategory(category)">
                <div class="text-lg font-medium">{{ category.title }}</div>
                <div class="text-md text-base-content/80 line-clamp-1"
                     v-if="category.description && category.description.length">
                  {{ category.description }}
                </div>
              </div>
            </div>
          </div>

          <!-- Products section -->
          <div v-if="filteredProducts.length > 0" class="mb-6">
            <h3 class="font-bold text-lg mb-2">{{ i18n.t('search.products') }}</h3>
            <div class="space-y-4">
              <ProductInList class="cursor-pointer hover:bg-warning/10 border-1 border-warning-content/40"
                             v-for="product in filteredProducts"
                             :key="`product-${product.id}`"
                             :product="product"
                             :preview="true"
                             :currency="currency"
                             @click="openProduct(product)"/>
            </div>
          </div>
        </div>

        <!-- No results -->
        <div v-else class="flex flex-col items-center justify-center py-5 px-9">
          <div class="text-lg text-base-content/80">{{ i18n.t('search.no_results') }}</div>
          <button class="btn btn-md text-base-content/80 mt-2 bg-base-100"
                  @click="clearSearch">
            {{ i18n.t('search.clear_query') }}
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="withPlaceholders" class="flex flex-col items-center justify-center py-10 px-9" >
        <!--        <Search class="w-16 h-16 mb-4 opacity-60" />-->
        <div class="w-full text-center text-lg text-base-content/80">{{ i18n.t('search.start_typing') }}</div>
      </div>
    </Deferred>
  </div>
</template>
