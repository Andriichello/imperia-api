<script setup lang="ts">
  import {computed, onUnmounted, PropType, ref, watch} from "vue";
  import {Dish, DishCategory, DishMenu, Restaurant} from "@/api";
  import {Flame, Leaf, Salad, Search} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";
  import Deferred from "@/Components/Deferred.vue";
  import ProductInListRightMedia from "@/Components/Menu/ProductInListRightMedia.vue";
  import LoadingProductInListRightMedia from "@/Components/Menu/LoadingProductInListRightMedia.vue";
  import {DISH_TAGS, type DishTag, matchesTag} from "@/flags";

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
    withPlaceholders: {
      type: Boolean,
      required: false,
      default: false,
    },
  });

  const emits = defineEmits(['open-menu', 'open-category', 'open-product', 'query-updated', 'tag-updated', 'has-results-changed']);

  const i18n = useI18n();

  const currency = computed(() => props.restaurant?.currency ?? 'uah');

  const searchQuery = ref("");

  const tag = ref<string>(null);

  // Tags offered as filters: the ones at least one dish has
  const availableTags = computed<DishTag[]>(
    () => DISH_TAGS.filter((t: DishTag) => props.products?.some((p: Dish) => matchesTag(p.flags, t.key)))
  );

  const filteredMenus = computed<DishMenu[]>(() => {
    if (!searchQuery.value?.length || tag.value !== null) {
      return [];
    }

    return props.menus?.filter(menu =>
      menu.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      menu.description?.toLowerCase().includes(searchQuery.value.toLowerCase())
    ) ?? [];
  });

  const filteredCategories = computed<DishCategory[]>(() => {
    if (!searchQuery.value?.length || tag.value !== null) {
      return [];
    }

    const filtered: DishCategory[] = [];

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

  const filteredProducts = computed<Dish[]>(() => {
    if (!searchQuery.value?.length && !tag.value?.length) {
      return [];
    }

    const filtered: Dish[] = [];

    props.products?.forEach(product => {
      const matchesFilter = !tag.value?.length || matchesTag(product.flags, tag.value);
      const matchesQuery = !searchQuery.value?.length || (
        product.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (product.description?.length && product.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
      );

      if ((matchesFilter && matchesQuery) && !filtered.includes(product)) {
        filtered.push(product);
      }
    });

    return filtered;
  });

  const hasResults = computed(() => {
    return props.products === null ||
           filteredMenus.value.length > 0 ||
           filteredCategories.value.length > 0 ||
           filteredProducts.value.length > 0;
  });

  function clearSearch() {
    searchQuery.value = "";
    tag.value = null;
  }

  function openMenu(menu: DishMenu) {
    emits('open-menu', menu);
  }

  function openCategory(category: DishCategory) {
    const menu: DishMenu | null = props.menus?.find((menu: DishMenu) => menu.categories.includes(category));

    if (menu) {
      emits('open-category', category, menu);
    }
  }

  function openProduct(product: Dish) {
    const menu = props.menus?.find(
      (m: DishMenu) => !!m.categories.find((c: DishCategory) => product.category_id === c.id)
    );
    const category = menu?.categories?.find(
      (c: DishCategory) => product.category_id === c.id
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

  watch(() => tag.value, (newVal) => {
    emits('tag-updated', newVal);
  });

  onUnmounted(() => {
    clearSearch();
  });
</script>

<template>
  <div class="max-w-full w-full flex flex-col">
    <!-- Search input -->
    <div class="relative flex flex-col justify-center items-start px-6">
      <div class="w-full flex items-center border-b border-base-300 pb-2">
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

    <Deferred :data="products">
      <template #fallback>
        <div class="flex flex-wrap justify-center gap-x-2 gap-y-1 normal-case text-[12px] text-base-content/60 pt-2 pb-1 px-2 opacity-60">
          <div class="w-[70px] h-[24px] px-1 flex justify-start items-center skeleton rounded-sm border-1 border-dashed">
            <Flame class="w-4 h-4"/>
          </div>

          <div class="w-[128px] h-[24px] px-1 flex justify-start items-center skeleton rounded-sm border-1 border-dashed">
            <Salad class="w-4 h-4"/>
          </div>

          <div class="w-[116px] h-[24px] px-1 flex justify-start items-center skeleton rounded-sm border-1 border-dashed">
            <Leaf class="w-4 h-4"/>
          </div>
        </div>

<!--        &lt;!&ndash; Loading indicator &ndash;&gt;-->
<!--        <div class="flex justify-center mt-5 mb-0 px-9">-->
<!--          <div class="loading loading-dots loading-lg text-warning/40"></div>-->
<!--        </div>-->
      </template>

      <div class="w-fit max-w-full flex justify-start gap-x-2 gap-y-1 normal-case text-[12px] pt-2 pb-1 px-2 overflow-x-auto no-scrollbar min-h-[38px] self-center"
           v-if="availableTags.length">
        <div
          class="rounded-sm border-1 border-dashed border-base-content flex flex-row justify-center items-center gap-1 text-base-content pl-1 pr-2 py-0.5 cursor-pointer"
          :class="{'opacity-45': tag !== t.key, 'bg-warning/30 text-warning-content-80': tag === t.key}"
          @click="tag = tag === t.key ? null : t.key"
          v-for="t in availableTags" :key="t.key">
          <component :is="t.icon" class="w-4 h-4"/>
          <p class="font-semibold pt-0.5 whitespace-nowrap">
            {{ i18n.t(t.label) }}
          </p>
        </div>
      </div>
    </Deferred>

    <!-- Search results -->
    <div v-if="searchQuery || tag?.length" class="overflow-auto pt-2 px-3">
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
            <div class="p-3 hover:bg-warning/10 border-1 border-warning-content/25 rounded-lg cursor-pointer transition-colors"
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
        <Deferred :data="products">
          <template #fallback>
            <div class="mb-6">
              <h3 class="font-bold text-lg mb-2">{{ i18n.t('search.products') }}</h3>
              <div class="space-y-2">
                <template v-for="n in 2" :key="n">
                  <LoadingProductInListRightMedia/>

                  <div class="w-full h-[1px] flex flex-col bg-warning-content/25"/>
                </template>
              </div>
            </div>
          </template>

          <div v-if="filteredProducts.length > 0" class="mb-6">
            <h3 class="font-bold text-lg mb-2">{{ i18n.t('search.products') }}</h3>
            <div class="space-y-2">
              <template v-for="product in filteredProducts" :key="`product-${product.id}`">
                <ProductInListRightMedia class="cursor-pointer hover:bg-warning/10"
                  :product="product"
                  :preview="true"
                  :currency="currency"
                  @click="openProduct(product)"/>

                <div class="w-full h-[1px] my-2 flex flex-col bg-warning-content/25"/>
              </template>
            </div>
          </div>
        </Deferred>
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
  </div>
</template>
