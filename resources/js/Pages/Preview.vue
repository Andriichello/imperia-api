<script setup lang="ts">
  import {ref, PropType, onMounted, onUnmounted, watch, computed} from "vue";
  import {Category, Menu, Product, Restaurant} from "@/api";
  import {Deferred, router} from "@inertiajs/vue3";
  import NavBar from "@/Components/Base/NavBar.vue";
  import SearchDrawer from "@/Components/Drawer/SearchDrawer.vue";
  import LanguageDrawer from "@/Components/Drawer/LanguageDrawer.vue";
  import {switchLanguage} from "@/i18n/utils";
  import {useI18n} from "vue-i18n";
  import BaseLayout from "@/Layouts/BaseLayout.vue";
  import DiagonalPattern from "@/Components/Base/DiagonalPattern.vue";
  import RestaurantComponent from "@/Components/Preview/RestaurantComponent.vue";
  import MenuInList from "@/Components/Menu/MenuInList.vue";
  import MenuNavBar from "@/Components/Menu/MenuNavBar.vue";
  import CategoryNavBar from "@/Components/Menu/CategoryNavBar.vue";
  import {getScheduleInfo, ScheduleInfo} from "@/helpers";
  import {useScrollLock} from "@/composables/useScrollLock";

  const props = defineProps({
    restaurant:  {
      type: Object as PropType<Restaurant>,
      required: true,
    },
    menus: {
      type: Array as PropType<Menu[]>,
      required: true,
    },
    products: {
      type: Array as PropType<Product[]>,
      default: null,
    },
    locale: {
      type: String,
      required: true,
    },
    supported_locales: {
      type: Array as PropType<string[]>,
      required: true,
    }
  });

  const i18n = useI18n();

  const isSearchOpened = ref(false);
  const isLanguageOpened = ref(false);

  const isSearchWithAutofocus = ref(true);

  const mode = ref<string>(window.location.pathname.includes('/menu') ? 'menu' : 'restaurant');

  const scheduleInfo = computed<ScheduleInfo>(
    () => getScheduleInfo(props.restaurant)
  );

  const getEstablishmentTitle = computed(() => {
    const establishment = props.restaurant?.establishment?.toLowerCase();

    if (!establishment) {
      return i18n.t('restaurant.title');
    }

    if (establishment.includes('café') || establishment.includes('cafe')) {
      return i18n.t('restaurant.cafe_title');
    }

    if (establishment.includes('bakery')) {
      return i18n.t('restaurant.bakery_title');
    }

    if (establishment.includes('bistro')) {
      return i18n.t('restaurant.bistro_title');
    }

    if (establishment.includes('pizzeria')) {
      return i18n.t('restaurant.pizzeria_title');
    }

    if (establishment.includes('bar')) {
      return i18n.t('restaurant.bar_title');
    }

    return i18n.t('restaurant.title');
  });

  const scheduleExpanded = ref(false);

  function resolveRestaurantId() {
    const match = window.location.pathname.match(/\/inertia\/([^\/]+)\/?/);
    return match ? parseInt(match[1]) : null;
  }

  function resolveMenuId() {
    const match = window.location.pathname.match(/\/menu\/(\d+)/);
    return match ? parseInt(match[1]) : null;
  }

  function resolveCategoryId() {
    let categoryId = window.location.hash.replace('#', '');

    if (categoryId.includes('-')) {
      const parts = categoryId.split('-');

      categoryId = parts[0];
    }

    return categoryId?.length > 0 ? parseInt(categoryId) : null;
  }

  function resolveProductId() {
    let categoryId = window.location.hash.replace('#', '');
    let productId = null;

    if (categoryId.includes('-')) {
      const parts = categoryId.split('-');

      productId = parts[1];
    }

    return productId?.length > 0 ? parseInt(productId) : null;
  }

  function resolveAllIds() {
    const resolvedRestaurantId = resolveRestaurantId();

    if (resolvedRestaurantId !== restaurantId.value) {
      restaurantId.value = resolvedRestaurantId;
    }

    const resolvedMenuId = resolveMenuId();

    if (resolvedMenuId !== menuId.value) {
      menuId.value = resolvedMenuId;
      selectedMenu.value = findMenu(resolvedMenuId);
    }

    const resolvedCategoryId = resolveCategoryId();

    if (resolvedCategoryId !== categoryId.value) {
      categoryId.value = resolvedCategoryId;
      selectedCategory.value = findCategory(resolvedCategoryId);
    }

    const resolvedProductId = resolveProductId();

    if (resolvedProductId !== productId.value) {
      productId.value = resolvedProductId;
      selectedProduct.value = findProduct(resolvedProductId);
    }
  }

  const restaurantId = ref<number>();
  const menuId = ref<number | null>();
  const categoryId = ref<number | null>();
  const productId = ref<number | null>();

  function findMenu(menuId: string|number|null) {
    if (menuId !== null) {
      return props.menus.find((m: Menu) => Number(m.id) === menuId)
    }

    return null;
  }

  function findCategory(categoryId: string|number|null) {
    if (categoryId !== null) {
      for (const menu of props.menus) {
        for (const category of menu.categories) {
          if (
            category.id === Number(categoryId) ||
            category.slug === String(categoryId)
          ) {
            return category;
          }
        }
      }
    }

    return null;
  }

  function findProduct(productId: string|number|null) {
    if (productId !== null) {
      for (const product of props.products) {
        if (product.id === Number(productId)) {
          return product;
        }
      }
    }

    return null;
  }

  const selectedMenu = ref<Menu>();
  const selectedCategory = ref<Category | null>();
  const selectedProduct = ref<Product | null>();

  const switchMenu = (menu: Menu, force: boolean = false) => {
    // Only update if it's a different menu
    if (force || menu.id !== selectedMenu.value.id) {
      const basePath = window.location.pathname.split('/menu/')[0];

      // Update the URL in the browser without a page reload
      router.replace({
        url: `${basePath}/menu/${menu.id}`,
        preserveState: true,
      });

      ignoringScroll.value = true;

      window.scrollTo({top: 0, behavior: 'smooth'});

      // Update your component's state locally
      // This is needed since we're not hitting the backend
      // You'd need to track the selected menu ID locally
      menuId.value = menu.id;
      selectedMenu.value = menu;
      categoryId.value = null;
      selectedCategory.value = null;
      productId.value = null;
      selectedProduct.value = null;

      const idToCheck = ignoringScrollId.value++;

      setTimeout(() => {
        if (idToCheck === (ignoringScrollId.value - 1)) {
          ignoringScroll.value = false;
        }
      }, 200)
    }
  };

  const switchCategory = (category: Category, product: Product | null = null) => {
    // Only update if it's a different menu
    if (category.id !== selectedCategory.value?.id) {
      // Update the URL in the browser without a page reload
      router.replace({
        url: window.location.pathname + '#' + category.id + (product ? '-' + product.id : ''),
        preserveState: true,
        preserveScroll: true,
      });

      // Update your component's state locally
      // This is needed since we're not hitting the backend
      // You'd need to track the selected menu ID locally
      categoryId.value = category.id;
      selectedCategory.value = category;
      productId.value = product?.id;
      selectedProduct.value = product;
    }
  };

  const stickyRef = ref<HTMLElement | null>(null);

  const ignoringScroll = ref(false);
  const ignoringScrollId = ref(0);

  const shouldNotScroll = ref(Date.now());
  const lastScrollPosition = ref(0);
  const continuousScroll = ref(0);
  const scrolledToSticky = ref(false);

  const showGoToTop = ref(false);
  const isGoingToTop = ref(false);

  const goToTop = () => {
    showGoToTop.value = false;
    ignoringScroll.value = true;
    isGoingToTop.value = true;

    selectedCategory.value = null;

    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });

    const idToCheck = ignoringScrollId.value++;

    setTimeout(() => {
      isGoingToTop.value = false;

      if (idToCheck === (ignoringScrollId.value - 1)) {
        ignoringScroll.value = false;
      }
    }, 1000)
  }

  const onScroll = () => {
    console.log('value: ', continuousScroll.value);

    // Get the current scroll position
    const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollPosition > 48) {
      if (!scrolledToSticky.value) {
        scrolledToSticky.value = true;
      }
    } else {
      if (scrolledToSticky.value) {
        scrolledToSticky.value = false;
      }
    }

    // Because of momentum scrolling on mobiles, we shouldn't continue if it is less than zero
    if (scrollPosition < 0) {
      lastScrollPosition.value = scrollPosition;
    }

    if (ignoringScroll.value) {
      lastScrollPosition.value = scrollPosition;
    }


    const isScrollingUp = (scrollPosition - lastScrollPosition.value) < 20;

    console.log('isScrollingUp: ', isScrollingUp);

    if (isScrollingUp) {
      if (continuousScroll.value < 0) {
        continuousScroll.value = 0;
      }

      continuousScroll.value += lastScrollPosition.value - scrollPosition;
    } else {
      if (continuousScroll.value > 0) {
        continuousScroll.value = 0;
      }

      continuousScroll.value -= scrollPosition - lastScrollPosition.value;
    }

    console.log('continuousScroll: ', continuousScroll.value);
    console.log('scrollPosition: ', scrollPosition);

    if (continuousScroll.value > 250) {
      showGoToTop.value = true;
    } else {
      showGoToTop.value = false;
    }

    if (scrollPosition < 0 || ignoringScroll.value) {
      return;
    }

    const categoriesCount = selectedMenu.value?.categories?.length ?? 0;

    for (let i = 0; i < categoriesCount; i++) {
      const category = selectedMenu.value.categories[i];
      const group = document.getElementById(`category-${category.id}`);

      if (!group) {
        continue;
      }

      const stickyHeight = stickyRef.value?.clientHeight ?? 96;

      const isTopOutOfView = group.offsetTop < scrollPosition
      const isBottomOutOfView = (group.offsetTop + group.clientHeight - stickyHeight) < scrollPosition;

      if (!isTopOutOfView || !isBottomOutOfView) {
        shouldNotScroll.value = Date.now();

        // select a category and scroll to it...
        switchCategory(category);
        break;
      }
    }

    lastScrollPosition.value = scrollPosition;
  };

  const scrollToCategory = (category: Category, product: Product | null = null) => {
    const divider = document.getElementById('category-' + category.id);

    if ((shouldNotScroll.value === 0 || (Date.now() - shouldNotScroll.value) > 100) && divider) {
      ignoringScroll.value = true;

      let top = divider.getBoundingClientRect().top;

      const stickyHeight = stickyRef.value?.clientHeight ?? 96;

      let productDivider = null;

      if (product) {
        top += (divider.children[0]?.clientHeight ?? 0) + 12;

        const productsContainer = document.getElementById('category-' + category.id + '-products');

        if (productsContainer) {
          productDivider = document.getElementById('product-' + product.id);

          if (productDivider) {
            let offset = 0;
            const children = productsContainer.children;

            for (let i = 0; i < children.length; i++) {
              const el = children[i];

              if (el.attributes.getNamedItem('id')?.value === 'product-' + product.id) {
                break;
              }

              offset += el.clientHeight + 12;
            }

            top += offset;
          }
        }
      }

      window.scrollTo({
        top: top + window.pageYOffset - stickyHeight + 4,
        behavior: 'smooth'
      });

      const idToCheck = ignoringScrollId.value++;

      setTimeout(() => {
        if (idToCheck === (ignoringScrollId.value - 1)) {
          ignoringScroll.value = false;
        }
      }, 1000)
    }

    shouldNotScroll.value = 0;
  }

  const onSwitchMenu = (menu: Menu) => {
    if (mode.value !== 'menu') {
      mode.value = 'menu';
    }

    isSearchOpened.value = false;

    switchMenu(menu, true);
  }
  const onSwitchCategory = (category: Category, menu: Menu = selectedMenu.value) => {
    if (mode.value !== 'menu') {
      mode.value = 'menu';
    }

    isSearchOpened.value = false;

    if (menu.id !== selectedMenu.value.id) {
      switchMenu(menu);
    }

    setTimeout(() => {
      switchCategory(category);
      scrollToCategory(category);
    }, 200);
  }

  const onSwitchProduct = (product: Product, category: Category, menu: Menu = selectedMenu.value) => {
    if (mode.value !== 'menu') {
      mode.value = 'menu';
    }

    isSearchOpened.value = false;

    if (menu.id !== selectedMenu.value.id) {
      switchMenu(menu);
    }

    setTimeout(() => {
      switchCategory(category, product);
      scrollToCategory(category, product);
    }, 200);
  }

  function onBackFromMenu() {
    mode.value = 'restaurant';

    router.replace({
      url: window.location.pathname.split('/menu/')[0],
      preserveState: true,
    });
  }

  function onOpenPhone(phone: string | null) {
    if (window && phone?.length > 0) {
      window.open(`tel:${phone}`, '_blank');
    }
  }

  function onOpenAddress(address: string | null) {
    if (window && address?.length > 0) {
      window.open(`https://www.google.com/maps/search/?api=1&query=${address}`, '_blank')
    }
  }

  // Methods for Drawers
  function onOpenSearch() {
    isSearchOpened.value = true;
    isSearchWithAutofocus.value = true;
  }

  function onOpenLanguage() {
    isLanguageOpened.value = true;
  }

  //  Event handlers for Drawers
  const onOpenMenu = (menu: Menu) => {
    isSearchOpened.value = false;
    mode.value = 'menu';

    menuId.value = menu.id;
    categoryId.value = null;
    productId.value = null;

    selectedMenu.value = menu;
    selectedCategory.value = null;
    selectedProduct.value = null;

    router.replace({
      url: window.location.pathname.split('/menu/')[0] + `/menu/${menu.id}`,
      preserveState: true,
    });
  }

  const onOpenCategory = (category: Category, menu: Menu = null) => {
    isSearchOpened.value = false;

    menu = menu ?? findMenu(menuId.value);

    menuId.value = menu.id;
    categoryId.value = category.id;
    productId.value = null;

    selectedMenu.value = menu;
    selectedCategory.value = category;
    selectedProduct.value = null;

    router.replace({
      url: window.location.pathname.split('/menu/')[0] + `/menu/${menu.id}`,
      preserveState: true,
    });
  }

  const onOpenProduct = (product: Product, category: Category, menu: Menu = null) => {
    isSearchOpened.value = false;
    menu = menu ?? findMenu(menuId.value);

    menuId.value = menu.id;
    categoryId.value = category.id;
    productId.value = product.id;

    selectedMenu.value = menu;
    selectedCategory.value = category;
    selectedProduct.value = product;
  }

  const onSwitchLanguage = (locale: string) => {
    switchLanguage(i18n, locale, true);
  }

  const { disableScroll, enableScroll } = useScrollLock()

  const isScrollDisabled = ref(false);

  function toggleScroll() {
    if (!isScrollDisabled.value) {
      disableScroll(document.documentElement);
      isScrollDisabled.value = true;
    } else {
      enableScroll()
      isScrollDisabled.value = false;
    }
  }


  onMounted(() => {
    window.addEventListener('scroll', onScroll);

    resolveAllIds();
  });

  onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
  });

  watch(() => props.products, (newValue, oldValue) => {
    if (oldValue) {
      return;
    }

    let categoryId = window.location.hash.replace('#', '');
    let productId = null;

    if (categoryId.includes('-')) {
      const parts = categoryId.split('-');

      categoryId = parts[0];
      productId = parts[1];
    }

    const category = findCategory(categoryId);
    const product = findProduct(productId);

    if (category) {
      setTimeout(() => {
        selectedCategory.value = category;

        if (selectedMenu.value.categories?.[0]?.id !== category.id || product) {
          shouldNotScroll.value = 0;
          ignoringScroll.value = false;
          scrollToCategory(category, product);
        }
      }, 100);
    }
  });

  watch(() => isSearchOpened.value, (newValue, oldValue) => {
    if (oldValue !== newValue) {
      toggleScroll();
    }
  });

  watch(() => isLanguageOpened.value, (newValue, oldValue) => {
    if (oldValue !== newValue) {
      toggleScroll();
    }
  });
</script>

<template>
  <BaseLayout>
    <div class="w-full max-w-md flex flex-col justify-center items-center">
      <template v-if="mode === 'restaurant'">
        <div class="w-full max-w-md absolute top-0 h-75 bg-base-200/20 border-b-1 border-base-300 overflow-hidden flex flex-col justify-center">
          <DiagonalPattern class="scale-165 opacity-60 text-warning-content/80"
                           :establishment="restaurant.establishment ?? 'restaurant'"/>
        </div>

        <RestaurantComponent :restaurant="restaurant"
                             :menus="menus"
                             @open-menu="onOpenMenu"
                             @openPhone="onOpenPhone"
                             @open-address="onOpenAddress"/>
      </template>

      <template v-else>
        <div class="h-13"/>

        <div class="w-full max-w-md flex flex-col justify-start items-center relative">
          <!-- Menus list -->
          <Deferred data="products">
            <template #fallback>
              <div class="loading loading-dots loading-lg mt-8 text-base-content/70"/>
            </template>

            <div class="w-full sticky top-0 bg-base-100 z-10 border-1 border-base-300"
                 ref="stickyRef"
                 :class="{'shadow-md': scrolledToSticky}">
              <MenuNavBar class="w-full"
                          :menus="menus"
                          :selected="selectedMenu"
                          @switch-menu="onSwitchMenu"
                          @open-drawer="isSearchWithAutofocus = false; isSearchOpened = true;"/>

              <CategoryNavBar class="w-full"
                              :categories="selectedMenu?.categories ?? []"
                              :selected="selectedCategory"
                              @switch-category="onSwitchCategory"/>
            </div>

<!--            <button class="btn btn-sm flex justify-center items-center sticky top-[100px] z-10 backdrop-blur-sm bg-neutral/35 text-white border-none uppercase"-->
<!--                    v-if="!isGoingToTop && scrolledToSticky"-->
<!--                    @click="goToTop">-->
<!--              go to top-->
<!--            </button>-->

            <div class="w-full flex flex-col pb-[250px]">
              <MenuInList :menu="selectedMenu"
                          :products="products"
                          :closed="false"
                          :establishment="restaurant.establishment ?? 'restaurant'"
                          @switch-menu="onSwitchMenu"
                          @switch-category="onSwitchCategory"/>
            </div>
          </Deferred>
        </div>
      </template>

      <div class="min-h-13 w-full max-w-md absolute top-0 p-2">
        <NavBar :back="mode === 'menu'"
                @on-back="onBackFromMenu"
                @on-search="onOpenSearch"
                @on-language="onOpenLanguage"/>
      </div>

      <SearchDrawer :open="isSearchOpened"
                    :restaurant="restaurant"
                    :menus="menus"
                    :products="products"
                    :with-autofocus="isSearchWithAutofocus"
                    @close="isSearchOpened = false"
                    @open-menu="onSwitchMenu"
                    @open-category="onSwitchCategory"
                    @open-product="onSwitchProduct"/>

      <LanguageDrawer :open="isLanguageOpened"
                      :locale="locale"
                      :supported_locales="supported_locales"
                      @close="isLanguageOpened = false"
                      @switch-language="onSwitchLanguage"/>
    </div>
  </BaseLayout>
</template>
