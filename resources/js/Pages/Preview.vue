<script setup lang="ts">
  import {ref, computed, PropType, onMounted, onUnmounted} from "vue";
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
  import CategoryNavBar from "@/Components/Menu/CategoryNavBar.vue";
  import MenuNavBar from "@/Components/Menu/MenuNavBar.vue";
  import MenuInList from "@/Components/Menu/MenuInList.vue";

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

  function resolveRestaurantId() {
    const match = window.location.pathname.match(/\/inertia\/([^\/]+)\//);
    return match ? parseInt(match[1]) : null;
  }

  const restaurantId = ref<number>(resolveRestaurantId()!);

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

  const menuId = ref<number | null>(resolveMenuId());
  const categoryId = ref<number | null>(resolveCategoryId());
  const productId = ref<number | null>(resolveProductId());

  const findCategory = (categoryId: string|number|null) => {
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

  const findProduct = (productId: string|number|null) => {
    if (productId !== null) {
      for (const product of products) {
        if (product.id === Number(productId)) {
          return product;
        }
      }
    }

    return null;
  }

  const selectedMenu = ref<Category | null>(
    props.menus.find((m: Menu) => Number(m.id) === menuId.value)
  );
  const selectedCategory = ref<Category | null>(
    findCategory(categoryId.value)
  );
  const selectedProduct = ref<Category | null>(
    findProduct(productId.value)
  );

  function onBackFromMenu() {
    mode.value = 'restaurant';

    menuId.value = null;
    categoryId.value = null;
    productId.value = null;

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

    selectedMenu.value = props.menus.find(m => m.id === menuId.value);
    selectedCategory.value = null;
    selectedCategory.value = null;

    router.replace({
      url: window.location.pathname.split('/menu/')[0] + `/menu/${menu.id}`,
      preserveState: true,
    });
  }

  const onOpenCategory = (category: Category, menu: Menu = selectedMenu.value) => {
    isSearchOpened.value = false;
  }

  const onOpenProduct = (product: Product, category: Category, menu: Menu = selectedMenu.value) => {
    isSearchOpened.value = false;
  }

  const onSwitchLanguage = (locale: string) => {
    switchLanguage(i18n, locale)
  }

  // Logic for Menu
  const stickyRef = ref<HTMLElement | null>(null);

  const ignoringScroll = ref(false);
  const ignoringScrollId = ref(0);

  const shouldNotScroll = ref(Date.now());
  const lastScrollPosition = ref(0);
  const continuousScroll = ref(0);
  const scrolledToSticky = ref(false);

  const onScroll = () => {
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
      return;
    }

    if (ignoringScroll.value) {
      lastScrollPosition.value = scrollPosition;
      return;
    }

    const isScrollingUp = scrollPosition < lastScrollPosition.value;

    if (isScrollingUp) {
      if (continuousScroll.value < 0) {
        continuousScroll.value = 0;
      }

      continuousScroll.value += lastScrollPosition.value - scrollPosition;
    }

    const categoriesCount = selectedMenu.value.categories.length;

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
      selectedCategory.value = category;
    }
  };

  const onSwitchCategory = (category: Category, menu: Menu = selectedMenu.value) => {
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
    isSearchOpened.value = false;

    if (menu.id !== selectedMenu.value.id) {
      switchMenu(menu);
    }

    setTimeout(() => {
      switchCategory(category, product);
      scrollToCategory(category, product);
    }, 200);
  }

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
      selectedMenu.value = menu;
      selectedCategory.value = null;

      const idToCheck = ignoringScrollId.value++;

      setTimeout(() => {
        if (idToCheck === (ignoringScrollId.value - 1)) {
          ignoringScroll.value = false;
        }
      }, 200)
    }
  };

  const onSwitchMenu = (menu: Menu) => {
    isSearchOpened.value = false;

    switchMenu(menu, true);
  }

  onMounted(() => {
    window.addEventListener('scroll', onScroll);
  });

  onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
  });
</script>

<template>
  <BaseLayout>
    <div class="w-full max-w-md flex flex-col justify-center items-center">
      <template v-if="mode === 'restaurant'">
        <div class="w-full max-w-md absolute top-0 h-75 bg-base-200/20 border-b-1 border-base-300 overflow-hidden flex flex-col justify-center">
          <DiagonalPattern class="scale-165 opacity-60"
                           :restaurant="restaurant"/>
        </div>

        <RestaurantComponent :restaurant="restaurant"
                             :menus="menus"
                             @open-menu="onOpenMenu"
                             @openPhone="onOpenPhone"
                             @open-address="onOpenAddress"/>
      </template>

      <template v-else>
        <div class="h-12"/>

        <div class="w-full max-w-md flex flex-col justify-start items-center relative">
          <div class="w-full sticky top-0 bg-base-100 z-10 border-1 border-base-300"
               ref="stickyRef"
               :class="{'shadow-md': scrolledToSticky}">
            <MenuNavBar class="w-full"
                        :menus="menus"
                        :selected="selectedMenu"
                        @switch-menu="onSwitchMenu"
                        @open-drawer="isSearchWithAutofocus = true; isSearchOpened = true"/>

            <CategoryNavBar class="w-full"
                            :categories="selectedMenu!.categories"
                            :selected="selectedCategory"
                            @switch-category="onSwitchCategory"/>
          </div>

          <!-- Menus list -->
          <Deferred data="products">
            <template #fallback>
              <div class="loading loading-dots loading-xl p-3 mt-5 text-base-content/70"></div>
            </template>

            <div class="w-full flex flex-col">
              <MenuInList :closed="false"
                          :menu="selectedMenu"
                          :products="products.filter((p: Product) => p.menu_ids?.includes(selectedMenu.id))"
                          :establishment="restaurant.establishment ?? 'restaurant'"
                          @switch-menu="onOpenMenu"
                          @switch-category="onOpenCategory"/>
            </div>
          </Deferred>
        </div>
      </template>

      <div class="w-full max-w-md absolute top-0 p-2">
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
                    @open-menu="onOpenMenu"
                    @open-category="onOpenCategory"
                    @open-product="onOpenProduct"/>

      <LanguageDrawer :open="isLanguageOpened"
                      :locale="locale"
                      :supported_locales="supported_locales"
                      @close="isLanguageOpened = false"
                      @switch-language="onSwitchLanguage"/>
    </div>
  </BaseLayout>
</template>
