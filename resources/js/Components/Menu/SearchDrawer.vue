<script setup lang="ts">
  import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";
  import { ref, watch, computed } from "vue";
  import { indexMenus, indexCategories, indexProducts } from "@/api";
  import { Menu, Category, Product } from "@/api";
  import { Search, X } from "lucide-vue-next";
  import { useI18n } from "vue-i18n";
  import ProductInList from "@/Components/Menu/ProductInList.vue";

  const props = defineProps({
    open: {
      type: Boolean,
      required: true,
    },
    currency: {
      type: String,
      default: "uah",
    },
  });

  const emits = defineEmits(['close', 'switch-menu', 'switch-category']);
  const { t } = useI18n();

  const searchQuery = ref("");
  const isLoading = ref(false);
  const menus = ref<Menu[]>([]);
  const categories = ref<Category[]>([]);
  const products = ref<Product[]>([]);

  const filteredMenus = computed(() => {
    if (!searchQuery.value) return [];
    return menus.value.filter(menu =>
      menu.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      menu.description?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  });

  const filteredCategories = computed(() => {
    if (!searchQuery.value) return [];
    return categories.value.filter(category =>
      category.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (category?.description && typeof category.description === 'object' &&
       category.description.text && category.description.text.toLowerCase().includes(searchQuery.value.toLowerCase()))
    );
  });

  const filteredProducts = computed(() => {
    if (!searchQuery.value) return [];
    return products.value;
  });

  const hasResults = computed(() => {
    return filteredMenus.value.length > 0 ||
           filteredCategories.value.length > 0 ||
           filteredProducts.value.length > 0;
  });

  function close() {
    searchQuery.value = "";
    emits('close');
  }

  function clearSearch() {
    searchQuery.value = "";
  }

  function switchMenu(menuId: number) {
    emits('switch-menu', menuId);
    close();
  }

  function switchCategory(categoryId: number) {
    emits('switch-category', categoryId);
    close();
  }

  async function fetchData() {
    isLoading.value = true;
    try {
      // Fetch menus and categories only once when the drawer opens
      if (menus.value.length === 0) {
        const menusResponse = await indexMenus();
        menus.value = menusResponse.data.data;
      }

      if (categories.value.length === 0) {
        const categoriesResponse = await indexCategories();
        categories.value = categoriesResponse.data.data;
      }

      // Fetch products with title filter
      if (searchQuery.value) {
        const productsResponse = await indexProducts({
          "filter[title]": searchQuery.value
        });
        products.value = productsResponse.data.data;
      } else {
        products.value = [];
      }

      console.log({menus: menus.value, categories: categories.value, products: products.value});
    } catch (error) {
      console.error("Error fetching search data:", error);
    } finally {
      isLoading.value = false;
    }
  }

  watch(() => props.open, (newValue) => {
    if (newValue) {
      fetchData();
    }
  }, { immediate: true });

  watch(searchQuery, () => {
    fetchData();
  });
</script>

<template>
  <BaseDrawer :open="open"
              @close="close">
    <div class="w-full h-full flex flex-col">
      <!-- Search input -->
      <div class="relative mb-4">
        <div class="flex items-center border-b border-base-300 pb-2">
          <Search class="w-5 h-5 text-base-content/60 mr-2" />
          <input
            v-model="searchQuery"
            type="text"
            :placeholder="t('search.placeholder')"
            class="w-full bg-transparent border-none focus:outline-none text-base-content"
            autocomplete="off"
          />
          <button
            v-if="searchQuery"
            @click="clearSearch"
            class="p-1"
          >
            <X class="w-5 h-5 text-base-content/60" />
          </button>
        </div>
      </div>

      <!-- Loading indicator -->
      <div v-if="isLoading" class="flex justify-center my-4">
        <div class="loading loading-spinner loading-md"></div>
      </div>

      <!-- Search results -->
      <div v-else-if="searchQuery" class="overflow-auto">
        <div v-if="hasResults">
          <!-- Menus section -->
          <div v-if="filteredMenus.length > 0" class="mb-6">
            <h3 class="font-bold text-lg mb-2">{{ t('search.menus') }}</h3>
            <div class="space-y-2">
              <div
                v-for="menu in filteredMenus"
                :key="`menu-${menu.id}`"
                @click="switchMenu(menu.id)"
                class="p-3 bg-base-200 rounded-lg cursor-pointer hover:bg-base-300 transition-colors"
              >
                <div class="font-medium">{{ menu.title }}</div>
                <div class="text-sm text-base-content/70 line-clamp-1">{{ menu.description }}</div>
              </div>
            </div>
          </div>

          <!-- Categories section -->
          <div v-if="filteredCategories.length > 0" class="mb-6">
            <h3 class="font-bold text-lg mb-2">{{ t('search.categories') }}</h3>
            <div class="space-y-2">
              <div
                v-for="category in filteredCategories"
                :key="`category-${category.id}`"
                @click="switchCategory(category.id)"
                class="p-3 bg-base-200 rounded-lg cursor-pointer hover:bg-base-300 transition-colors"
              >
                <div class="font-medium">{{ category.title }}</div>
                <div v-if="category.description && category.description.text" class="text-sm text-base-content/70 line-clamp-1">
                  {{ category.description.text }}
                </div>
              </div>
            </div>
          </div>

          <!-- Products section -->
          <div v-if="filteredProducts.length > 0" class="mb-6">
            <h3 class="font-bold text-lg mb-2">{{ t('search.products') }}</h3>
            <div class="space-y-4">
              <ProductInList
                v-for="product in filteredProducts"
                :key="`product-${product.id}`"
                :product="product"
                :currency="currency"
              />
            </div>
          </div>
        </div>

        <!-- No results -->
        <div v-else class="flex flex-col items-center justify-center py-10">
          <div class="text-lg text-base-content/70">{{ t('search.no_results') }}</div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="flex flex-col items-center justify-center py-10">
        <Search class="w-16 h-16 text-base-content/30 mb-4" />
        <div class="text-lg text-base-content/70">{{ t('search.start_typing') }}</div>
      </div>
    </div>
  </BaseDrawer>
</template>
