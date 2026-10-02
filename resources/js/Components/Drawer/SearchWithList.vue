<script setup lang="ts">
  import {computed, onUnmounted, PropType, ref, watch} from "vue";
  import {Dish, DishCategory, DishMenu, Restaurant} from "@/api";
  import {Ban, ChevronDown, ChevronUp, Flame, Funnel, Search, X} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";
  import Deferred from "@/Components/Deferred.vue";
  import ProductInListRightMedia from "@/Components/Menu/ProductInListRightMedia.vue";
  import LoadingProductInListRightMedia from "@/Components/Menu/LoadingProductInListRightMedia.vue";
  import {ALLERGENS, DISH_TAGS, type DishTag, getAllergenLabel, getAllergens, hasTag, matchesTag} from "@/flags";

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

  const emits = defineEmits(['open-menu', 'open-category', 'open-product', 'query-updated', 'filtering-changed', 'has-results-changed']);

  type Spiciness = 'spicy' | 'not-spicy';

  /** Diet filters, in this order; a dish has to match all the selected ones. */
  const DIETS = ['vegetarian', 'vegan'];

  /** Looks of a filter chip, selected or not. */
  const CHIP_ON = 'bg-primary/20 border-primary/40 text-primary-content';
  const CHIP_OFF = 'bg-base-100 border-zinc-300 text-base-content';
  const CHIP_EXCLUDED = 'bg-[#fbefeb] border-orange-700 text-orange-700';

  const i18n = useI18n();

  const currency = computed(() => props.restaurant?.currency ?? 'uah');

  const searchQuery = ref("");

  interface Filters {
    diets: string[],
    spiciness: Spiciness | null,
    // Allergen flags of the ingredients to exclude
    excluded: string[],
  }

  const noFilters = (): Filters => ({diets: [], spiciness: null, excluded: []});

  const hasAny = (filters: Filters): boolean =>
    filters.diets.length > 0 || filters.spiciness !== null || filters.excluded.length > 0;

  // The filters the results are filtered by
  const applied = ref<Filters>(noFilters());
  // The selections in the open filters, applied with "Show N dishes"
  const draft = ref<Filters>(noFilters());

  const filtersOpen = ref(false);

  const anyFilter = computed<boolean>(() => hasAny(applied.value));

  const searching = computed<boolean>(() => searchQuery.value.length > 0 || anyFilter.value);

  // Filters offered: the ones at least one dish has
  const dietOptions = computed<DishTag[]>(() => DIETS
    .map((key) => DISH_TAGS.find((t: DishTag) => t.key === key)!)
    .filter((t: DishTag) => props.products?.some((p: Dish) => matchesTag(p.flags, t.key))));

  const offersSpiciness = computed<boolean>(
    () => !!props.products?.some((p: Dish) => hasTag(p.flags, 'hotness'))
  );

  const allergenOptions = computed<string[]>(
    () => ALLERGENS.filter((flag) => props.products?.some((p: Dish) => p.flags?.includes(flag)))
  );

  const offersFilters = computed<boolean>(
    () => dietOptions.value.length > 0 || offersSpiciness.value || allergenOptions.value.length > 0
  );

  function allergenName(flag: string): string {
    return i18n.t(getAllergenLabel(flag));
  }

  function spicinessLabel(value: Spiciness): string {
    return i18n.t(value === 'spicy' ? 'search.spicy' : 'search.not_spicy');
  }

  /** Applied filters, as shown in the collapsed banner; removing one applies right away. */
  const selectedFilters = computed(() => [
    ...applied.value.diets.map((key) => ({
      key,
      label: i18n.t(DISH_TAGS.find((t: DishTag) => t.key === key)!.label),
      excluding: false,
      remove: () => applied.value = {...applied.value, diets: applied.value.diets.filter((k) => k !== key)},
    })),
    ...(applied.value.spiciness ? [{
      key: applied.value.spiciness,
      label: spicinessLabel(applied.value.spiciness),
      excluding: false,
      remove: () => applied.value = {...applied.value, spiciness: null},
    }] : []),
    ...applied.value.excluded.map((flag) => ({
      key: flag,
      label: i18n.t('search.without', {name: allergenName(flag).toLocaleLowerCase()}),
      excluding: true,
      remove: () => applied.value = {...applied.value, excluded: applied.value.excluded.filter((f) => f !== flag)},
    })),
  ]);

  function toggle(list: string[], item: string): string[] {
    return list.includes(item) ? list.filter((i) => i !== item) : [...list, item];
  }

  function toggleDiet(key: string) {
    draft.value = {...draft.value, diets: toggle(draft.value.diets, key)};
  }

  function toggleSpiciness(value: Spiciness) {
    draft.value = {...draft.value, spiciness: draft.value.spiciness === value ? null : value};
  }

  function toggleExcluded(flag: string) {
    draft.value = {...draft.value, excluded: toggle(draft.value.excluded, flag)};
  }

  /** Opens the filters with the applied ones selected. */
  function openFilters() {
    draft.value = {...applied.value};
    filtersOpen.value = true;
  }

  /** Closes the filters without applying the selections. */
  function closeFilters() {
    filtersOpen.value = false;
  }

  function applyFilters() {
    applied.value = {...draft.value};
    filtersOpen.value = false;
  }

  const filteredMenus = computed<DishMenu[]>(() => {
    if (!searchQuery.value?.length || anyFilter.value) {
      return [];
    }

    return props.menus?.filter(menu =>
      menu.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      menu.description?.toLowerCase().includes(searchQuery.value.toLowerCase())
    ) ?? [];
  });

  const filteredCategories = computed<DishCategory[]>(() => {
    if (!searchQuery.value?.length || anyFilter.value) {
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

  /** Dishes matching the query. */
  const queriedProducts = computed<Dish[]>(() => {
    const query = searchQuery.value.toLowerCase();

    return props.products?.filter((product: Dish) => !query.length ||
      product.title.toLowerCase().includes(query) ||
      !!product.description?.toLowerCase().includes(query)
    ) ?? [];
  });

  /** Whether a dish matches the diet and spiciness of the filters. */
  function matchesDietAndSpiciness(product: Dish, filters: Filters): boolean {
    return filters.diets.every((key) => matchesTag(product.flags, key)) &&
      (filters.spiciness === null || hasTag(product.flags, 'hotness') === (filters.spiciness === 'spicy'));
  }

  function containsExcluded(product: Dish, filters: Filters): boolean {
    return getAllergens(product.flags).some((flag) => filters.excluded.includes(flag));
  }

  /** Dishes matching the query, diet and spiciness, before excluding ingredients. */
  const matchingProducts = computed<Dish[]>(
    () => queriedProducts.value.filter((p) => matchesDietAndSpiciness(p, applied.value))
  );

  const filteredProducts = computed<Dish[]>(
    () => matchingProducts.value.filter((p) => !containsExcluded(p, applied.value))
  );

  const hiddenProducts = computed<Dish[]>(
    () => matchingProducts.value.filter((p) => containsExcluded(p, applied.value))
  );

  /** How many dishes the selections in the open filters would show. */
  const draftCount = computed<number>(() => queriedProducts.value
    .filter((p) => matchesDietAndSpiciness(p, draft.value) && !containsExcluded(p, draft.value))
    .length);

  /** The excluded ingredients the hidden dishes contain, e.g. "milk, eggs". */
  const hiddenBecauseOf = computed<string>(() => ALLERGENS
    .filter((flag) => applied.value.excluded.includes(flag) &&
      hiddenProducts.value.some((p: Dish) => p.flags?.includes(flag)))
    .map((flag) => allergenName(flag).toLocaleLowerCase())
    .join(', '));

  const hasResults = computed(() => {
    return props.products === null || (searching.value && (
      filteredMenus.value.length > 0 ||
      filteredCategories.value.length > 0 ||
      filteredProducts.value.length > 0 ||
      hiddenProducts.value.length > 0
    ));
  });

  function clearSearch() {
    searchQuery.value = "";
    applied.value = noFilters();
    draft.value = noFilters();
    filtersOpen.value = false;
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

  watch(() => anyFilter.value || filtersOpen.value, (newVal) => {
    emits('filtering-changed', newVal);
  });

  onUnmounted(() => {
    clearSearch();
  });
</script>

<template>
  <div class="max-w-full w-full flex flex-col">
    <!-- Search input -->
    <div class="px-5">
      <div class="relative h-12 flex items-center border-b border-zinc-200">
        <Search class="size-5 shrink-0 mr-2 text-base-content/65"/>
        <label for="searchInputRef" class="sr-only">{{ i18n.t('search.label') }}</label>
        <input
          v-model="searchQuery"
          id="searchInputRef"
          ref="searchInputRef"
          type="text"
          :placeholder="i18n.t('search.placeholder')"
          class="w-full p-0 bg-transparent border-none outline-none text-lg/7 text-base-content"
          autocomplete="off"
          @focus="closeFilters"
        />
      </div>
    </div>

    <Deferred :data="products">
      <template #fallback>
        <div class="mt-3 min-h-13 shrink-0 flex items-center gap-2.5 px-4 border-y border-primary/40 text-primary-content/50">
          <Funnel class="size-[18px] shrink-0"/>
          <span class="flex-1 text-sm/5 font-semibold">{{ i18n.t('search.filter_banner') }}</span>
        </div>
      </template>

      <template v-if="offersFilters">
        <!-- Expanded: the banner stays on top and the filters fill the rest of the drawer -->
        <div class="mt-3 flex-1 min-h-0 flex flex-col"
             role="region"
             :aria-label="i18n.t('search.filters')"
             v-if="filtersOpen">
          <button type="button"
                  class="min-h-13 shrink-0 flex items-center gap-2.5 px-4 border-y border-primary/40 text-primary-content text-start cursor-pointer"
                  aria-expanded="true"
                  @click="closeFilters">
            <Funnel class="size-[18px] shrink-0"/>
            <span class="flex-1 text-sm/5 font-semibold">{{ i18n.t('search.filter_banner') }}</span>
            <ChevronUp class="size-5 shrink-0"/>
          </button>

          <div class="flex-1 min-h-0 overflow-auto flex flex-col gap-5 pt-4 px-4 pb-4">
            <div class="flex flex-col gap-2"
                 v-if="dietOptions.length">
              <p class="text-[13px]/5 font-semibold text-base-content/68">{{ i18n.t('search.diet') }}</p>

              <div class="flex flex-wrap gap-2">
                <button type="button"
                        class="h-10 inline-flex items-center gap-1.5 pl-2.5 pr-3 rounded-lg border text-sm font-semibold cursor-pointer"
                        :class="draft.diets.includes(t.key) ? CHIP_ON : CHIP_OFF"
                        :aria-pressed="draft.diets.includes(t.key)"
                        v-for="t in dietOptions" :key="t.key"
                        @click="toggleDiet(t.key)">
                  <component :is="t.icon" class="size-4 shrink-0"/>
                  {{ i18n.t(t.label) }}
                </button>
              </div>
            </div>

            <div class="flex flex-col gap-2"
                 v-if="offersSpiciness">
              <p class="text-[13px]/5 font-semibold text-base-content/68">{{ i18n.t('search.spiciness') }}</p>

              <div class="flex flex-wrap gap-2">
                <button type="button"
                        class="h-10 inline-flex items-center gap-1.5 pl-2.5 pr-3 rounded-lg border text-sm font-semibold cursor-pointer"
                        :class="draft.spiciness === 'spicy' ? CHIP_ON : CHIP_OFF"
                        :aria-pressed="draft.spiciness === 'spicy'"
                        @click="toggleSpiciness('spicy')">
                  <Flame class="size-4 shrink-0"/>
                  {{ spicinessLabel('spicy') }}
                </button>

                <button type="button"
                        class="h-10 inline-flex items-center px-3 rounded-lg border text-sm font-semibold cursor-pointer"
                        :class="draft.spiciness === 'not-spicy' ? CHIP_ON : CHIP_OFF"
                        :aria-pressed="draft.spiciness === 'not-spicy'"
                        @click="toggleSpiciness('not-spicy')">
                  {{ spicinessLabel('not-spicy') }}
                </button>
              </div>
            </div>

            <div class="flex flex-col gap-2"
                 v-if="allergenOptions.length">
              <p class="text-[13px]/5 font-semibold text-base-content/68">{{ i18n.t('search.exclude_ingredients') }}</p>

              <div class="flex flex-wrap gap-2">
                <button type="button"
                        class="h-10 inline-flex items-center gap-1.5 pl-2.5 pr-3 rounded-lg border text-sm font-semibold cursor-pointer"
                        :class="draft.excluded.includes(flag) ? CHIP_EXCLUDED : CHIP_OFF"
                        :aria-pressed="draft.excluded.includes(flag)"
                        v-for="flag in allergenOptions" :key="flag"
                        @click="toggleExcluded(flag)">
                  <Ban class="size-4 shrink-0"
                       :class="{'text-base-content/65': !draft.excluded.includes(flag)}"/>
                  {{ allergenName(flag) }}
                </button>
              </div>
            </div>
          </div>

          <div class="shrink-0 flex flex-col gap-2 pt-3 px-4 pb-5 border-t border-primary/40">
            <button type="button"
                    class="w-full h-11 flex items-center justify-center rounded-lg border border-zinc-300 bg-base-100 text-base-content text-base font-semibold cursor-pointer disabled:cursor-default disabled:opacity-50"
                    :disabled="!hasAny(draft)"
                    @click="draft = noFilters()">
              {{ i18n.t('search.clear_all') }}
            </button>

            <button type="button"
                    class="w-full h-13 flex items-center justify-center rounded-lg bg-primary-content text-base-100 text-base font-semibold cursor-pointer"
                    v-if="draftCount"
                    @click="applyFilters">
              {{ i18n.t('search.show_dishes', {count: draftCount}, draftCount) }}
            </button>

            <div class="h-13 flex items-center justify-center rounded-lg bg-zinc-200 text-base-content/72 text-base font-semibold"
                 role="status"
                 v-else>
              {{ i18n.t('search.no_matching_dishes') }}
            </div>
          </div>
        </div>

        <!-- Collapsed, with the applied filters -->
        <div class="mt-3 min-h-13 shrink-0 flex items-center gap-2.5 py-1 pr-1 pl-4 border-y border-primary/40 text-primary-content cursor-pointer"
             v-else-if="anyFilter"
             @click="openFilters">
          <Funnel class="size-[18px] shrink-0"/>

          <div class="no-scrollbar flex-1 min-w-0 flex items-center gap-1.5 overflow-x-auto">
            <span class="h-9 shrink-0 inline-flex items-center pl-2.5 rounded-lg border text-sm font-semibold"
                  :class="filter.excluding ? CHIP_EXCLUDED : CHIP_ON"
                  v-for="filter in selectedFilters" :key="filter.key">
              {{ filter.label }}

              <button type="button"
                      class="size-[34px] flex items-center justify-center cursor-pointer"
                      :aria-label="i18n.t('search.remove_filter', {name: filter.label})"
                      @click.stop="filter.remove()">
                <X class="size-4"/>
              </button>
            </span>
          </div>

          <button type="button"
                  class="size-11 shrink-0 flex items-center justify-center cursor-pointer"
                  :aria-label="i18n.t('search.edit_filters')"
                  aria-expanded="false"
                  @click.stop="openFilters">
            <ChevronDown class="size-5"/>
          </button>
        </div>

        <!-- Collapsed, nothing applied -->
        <button type="button"
                class="mt-3 min-h-13 shrink-0 flex items-center gap-2.5 px-4 border-y border-primary/40 text-primary-content text-start cursor-pointer"
                aria-expanded="false"
                v-else
                @click="openFilters">
          <Funnel class="size-[18px] shrink-0"/>
          <span class="flex-1 text-sm/5 font-semibold">{{ i18n.t('search.filter_banner') }}</span>
          <ChevronDown class="size-5 shrink-0"/>
        </button>
      </template>
    </Deferred>

    <!-- Search results -->
    <div class="flex-1 min-h-0 overflow-auto pt-3 px-3 pb-[250px]"
         v-if="searching && !filtersOpen">
      <template v-if="hasResults">
        <!-- Menus section -->
        <div v-if="filteredMenus.length > 0" class="mb-6">
          <h3 class="text-base/6 font-bold mb-2">{{ i18n.t('search.menus') }}</h3>
          <div class="flex flex-col gap-2">
            <div class="p-3 border border-zinc-200 rounded-lg cursor-pointer"
                 v-for="menu in filteredMenus"
                 :key="`menu-${menu.id}`"
                 @click="openMenu(menu)">
              <div class="text-lg/7 font-semibold">{{ menu.title }}</div>
              <div class="text-[15px]/[22px] text-base-content/65 line-clamp-1"
                   v-if="menu.description?.length">
                {{ menu.description }}
              </div>
            </div>
          </div>
        </div>

        <!-- Categories section -->
        <div v-if="filteredCategories.length > 0" class="mb-6">
          <h3 class="text-base/6 font-bold mb-2">{{ i18n.t('search.categories') }}</h3>
          <div class="flex flex-col gap-2">
            <div class="p-3 border border-zinc-200 rounded-lg cursor-pointer"
                 v-for="category in filteredCategories"
                 :key="`category-${category.id}`"
                 @click="openCategory(category)">
              <div class="text-lg/7 font-semibold">{{ category.title }}</div>
              <div class="text-[15px]/[22px] text-base-content/65 line-clamp-1"
                   v-if="category.description?.length">
                {{ category.description }}
              </div>
            </div>
          </div>
        </div>

        <!-- Dishes section -->
        <Deferred :data="products">
          <template #fallback>
            <div class="flex flex-col gap-1">
              <h3 class="text-base/6 font-bold">{{ i18n.t('search.products') }}</h3>

              <template v-for="n in 2" :key="n">
                <LoadingProductInListRightMedia class="px-0!"/>

                <div class="h-px bg-[#e8e8e8]"/>
              </template>
            </div>
          </template>

          <div class="flex flex-col gap-1"
               v-if="filteredProducts.length || hiddenProducts.length">
            <h3 class="text-base/6 font-bold">
              {{ i18n.t('search.dishes_count', {count: filteredProducts.length}, filteredProducts.length) }}
            </h3>

            <template v-for="product in filteredProducts" :key="`product-${product.id}`">
              <ProductInListRightMedia class="cursor-pointer"
                                       :product="product"
                                       :preview="true"
                                       :flush="true"
                                       :currency="currency"
                                       :establishment="restaurant?.establishment ?? 'restaurant'"
                                       @click="openProduct(product)"/>

              <div class="h-px bg-[#e8e8e8]"/>
            </template>

            <div class="mt-2 flex items-center justify-between gap-3 py-1 pr-1 pl-3.5 rounded-lg bg-zinc-100 text-sm/5 text-base-content/72"
                 v-if="hiddenProducts.length">
              <span>
                {{ i18n.t('search.hidden_contains', {count: hiddenProducts.length, names: hiddenBecauseOf}, hiddenProducts.length) }}
              </span>

              <button type="button"
                      class="h-11 shrink-0 px-3 font-semibold text-primary-content cursor-pointer"
                      @click="applied = {...applied, excluded: []}">
                {{ i18n.t('search.show') }}
              </button>
            </div>
          </div>
        </Deferred>
      </template>

      <!-- No results -->
      <div v-else class="flex flex-col items-center justify-center py-5 px-9">
        <div class="text-lg text-base-content/72">{{ i18n.t('search.no_results') }}</div>
        <button type="button"
                class="h-11 px-4 mt-2 rounded border border-zinc-200 bg-base-100 font-semibold text-base-content/72 cursor-pointer"
                @click="clearSearch">
          {{ i18n.t('search.clear_query') }}
        </button>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else-if="withPlaceholders && !filtersOpen" class="flex flex-col items-center justify-center py-10 px-9" >
      <div class="w-full text-center text-lg text-base-content/72">{{ i18n.t('search.start_typing') }}</div>
    </div>
  </div>
</template>
