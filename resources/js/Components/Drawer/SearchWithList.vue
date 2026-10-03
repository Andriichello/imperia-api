<script setup lang="ts">
  import {computed, onUnmounted, PropType, ref, watch} from "vue";
  import {Dish, DishCategory, DishMenu, Restaurant} from "@/api";
  import {Check, ChevronDown, ChevronUp, Flame, Funnel, Search, X} from "lucide-vue-next";
  import {useI18n} from "vue-i18n";
  import Deferred from "@/Components/Deferred.vue";
  import ProductInListRightMedia from "@/Components/Menu/ProductInListRightMedia.vue";
  import LoadingProductInListRightMedia from "@/Components/Menu/LoadingProductInListRightMedia.vue";
  import {type DishTag, findTag, HOTNESS, matchesTag, OPPOSITE_TAGS, TAG_GROUPS, tagsOf} from "@/flags";

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

  /** Looks of a filter chip, selected or not. */
  const CHIP_ON = 'bg-primary/20 border-primary/40 text-primary-content';
  const CHIP_OFF = 'bg-base-100 border-zinc-300 text-base-content';

  const i18n = useI18n();

  const currency = computed(() => props.restaurant?.currency ?? 'uah');

  const searchQuery = ref("");

  // Tags of the dishes the results are filtered by
  const applied = ref<string[]>([]);
  // The tags picked in the open filters, applied with "Show N dishes"
  const draft = ref<string[]>([]);

  const filtersOpen = ref(false);

  const anyFilter = computed<boolean>(() => applied.value.length > 0);

  const searching = computed<boolean>(() => searchQuery.value.length > 0 || anyFilter.value);

  // Filters offered, by their groups: the tags at least one dish matches
  const filterGroups = computed(() => TAG_GROUPS
    .map((group) => ({
      group,
      tags: tagsOf(group).filter((tag: DishTag) => props.products?.some((p: Dish) => matchesTag(p.flags, tag.key))),
    }))
    .filter(({tags}) => tags.length > 0));

  const offersFilters = computed<boolean>(() => filterGroups.value.length > 0);

  /** Applied filters, as shown in the collapsed banner; removing one applies right away. */
  const selectedFilters = computed<DishTag[]>(
    () => applied.value.map((key) => findTag(key)).filter((tag): tag is DishTag => tag !== null)
  );

  function removeFilter(key: string) {
    applied.value = applied.value.filter((other) => other !== key);
  }

  /**
   * Picks the tag in the open filters, or unpicks it. Spicy is any level of hotness, so it doesn't
   * go with the levels; low and high of the same thing don't go together either.
   */
  function toggleTag(key: string) {
    if (draft.value.includes(key)) {
      draft.value = draft.value.filter((other) => other !== key);
      return;
    }

    const dropped = key === 'hotness' ? HOTNESS : [HOTNESS.includes(key) ? 'hotness' : OPPOSITE_TAGS[key]];

    draft.value = [...draft.value.filter((other) => !dropped.includes(other)), key];
  }

  /** Opens the filters with the applied ones selected. */
  function openFilters() {
    draft.value = [...applied.value];
    filtersOpen.value = true;
  }

  /** Closes the filters without applying the selections. */
  function closeFilters() {
    filtersOpen.value = false;
  }

  function applyFilters() {
    applied.value = [...draft.value];
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

  /** Whether a dish matches the tags: all of them, but any one of the levels of hotness. */
  function matchesFilters(product: Dish, keys: string[]): boolean {
    const levels = keys.filter((key) => HOTNESS.includes(key));

    return keys.every((key) => HOTNESS.includes(key) || matchesTag(product.flags, key)) &&
      (!levels.length || levels.some((key) => matchesTag(product.flags, key)));
  }

  const filteredProducts = computed<Dish[]>(
    () => queriedProducts.value.filter((p) => matchesFilters(p, applied.value))
  );

  /** How many dishes the tags picked in the open filters would show. */
  const draftCount = computed<number>(
    () => queriedProducts.value.filter((p) => matchesFilters(p, draft.value)).length
  );

  const hasResults = computed(() => {
    return props.products === null || (searching.value && (
      filteredMenus.value.length > 0 ||
      filteredCategories.value.length > 0 ||
      filteredProducts.value.length > 0
    ));
  });

  function clearSearch() {
    searchQuery.value = "";
    applied.value = [];
    draft.value = [];
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
            <span class="min-w-[22px] h-[22px] shrink-0 inline-flex items-center justify-center px-1.5 rounded-full bg-primary-content text-base-100 text-xs/4 font-bold"
                  v-if="draft.length">
              <span aria-hidden="true">{{ draft.length }}</span>
              <span class="sr-only">{{ i18n.t('search.filters_on', {count: draft.length}, draft.length) }}</span>
            </span>
            <ChevronUp class="size-5 shrink-0"/>
          </button>

          <div class="flex-1 min-h-0 overflow-auto flex flex-col gap-5 pt-4 px-4 pb-4">
            <div class="flex flex-col gap-2"
                 role="group"
                 :aria-label="i18n.t(`search.${group}`)"
                 v-for="{group, tags} in filterGroups" :key="group">
              <p class="text-[13px]/5 font-semibold text-base-content/68">
                {{ i18n.t(`search.${group}`) }}<span class="font-normal text-base-content/60" v-if="group === 'spiciness'"> · {{ i18n.t('search.pick_several') }}</span>
              </p>

              <div class="flex flex-wrap gap-2">
                <!-- levels of hotness show 1–4 flames after the label -->
                <button type="button"
                        class="h-10 inline-flex items-center gap-1.5 pl-2.5 pr-3 rounded-lg border text-sm font-semibold whitespace-nowrap cursor-pointer"
                        :class="draft.includes(tag.key) ? CHIP_ON : CHIP_OFF"
                        :aria-pressed="draft.includes(tag.key)"
                        :aria-label="tag.level ? i18n.t('search.level', {name: i18n.t(tag.label), level: tag.level}) : undefined"
                        v-for="tag in tags" :key="tag.key"
                        @click="toggleTag(tag.key)">
                  <Check class="size-4 shrink-0 stroke-3" v-if="draft.includes(tag.key)"/>
                  <component :is="tag.icon" class="size-4 shrink-0 text-base-content/60" v-else-if="!tag.level"/>
                  {{ i18n.t(tag.label) }}
                  <span class="inline-flex"
                        :class="draft.includes(tag.key) ? 'text-primary-content' : 'text-base-content/60'"
                        aria-hidden="true"
                        v-if="tag.level">
                    <Flame class="size-[13px] shrink-0 not-first:-ml-0.5" v-for="n in tag.level" :key="n"/>
                  </span>
                </button>
              </div>
            </div>
          </div>

          <div class="shrink-0 flex flex-col gap-2 pt-3 px-4 pb-5 border-t border-primary/40">
            <button type="button"
                    class="w-full h-11 flex items-center justify-center rounded-lg border border-zinc-300 bg-base-100 text-base-content text-base font-semibold cursor-pointer"
                    @click="draft = []">
              {{ i18n.t('search.clear_all') }}
            </button>

            <button type="button"
                    class="w-full h-13 flex items-center justify-center rounded-lg bg-primary-content text-base-100 text-base font-semibold cursor-pointer"
                    v-if="draftCount"
                    @click="applyFilters">
              {{ i18n.t('search.show_dishes', {count: draftCount}, draftCount) }}
            </button>

            <div class="h-13 flex items-center justify-center rounded-lg bg-zinc-200 text-zinc-600 text-base font-semibold"
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
            <span class="h-9 shrink-0 inline-flex items-center gap-1.5 pl-2.5 rounded-lg border text-sm font-semibold"
                  :class="CHIP_ON"
                  v-for="tag in selectedFilters" :key="tag.key">
              <component :is="tag.icon" class="size-4 shrink-0"/>
              {{ i18n.t(tag.label) }}

              <button type="button"
                      class="w-[30px] h-[34px] -ml-0.5 flex items-center justify-center cursor-pointer"
                      :aria-label="i18n.t('search.remove_filter', {name: i18n.t(tag.label)})"
                      @click.stop="removeFilter(tag.key)">
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
               v-if="filteredProducts.length">
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
