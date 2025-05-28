<script setup lang="ts">
  import {ref, computed, PropType} from "vue";
  import {Category, Menu, Product, Restaurant} from "@/api";
  import {router} from "@inertiajs/vue3";
  import NavBar from "@/Components/Base/NavBar.vue";
  import SearchDrawer from "@/Components/Drawer/SearchDrawer.vue";
  import LanguageDrawer from "@/Components/Drawer/LanguageDrawer.vue";
  import {switchLanguage} from "@/i18n/utils";
  import {useI18n} from "vue-i18n";
  import BaseLayout from "@/Layouts/BaseLayout.vue";
  import DiagonalPattern from "@/Components/Base/DiagonalPattern.vue";
  import RestaurantComponent from "@/Components/Preview/RestaurantComponent.vue";

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

  const mode = computed(() => {
    return window.location.pathname.includes('/menu')
      ? 'menu' : 'restaurant';
  });

  const restaurantId = computed(() => {
    const match = window.location.pathname.match(/\/inertia\/([^\/]+)\//);
    return match ? parseInt(match[1]) : null;
  });

  const menuId = computed(() => {
    const match = window.location.pathname.match(/\/menu\/(\d+)/);
    return match ? parseInt(match[1]) : null;
  });

  const categoryId = computed(() => {
    let categoryId = window.location.hash.replace('#', '');

    if (categoryId.includes('-')) {
      const parts = categoryId.split('-');

      categoryId = parts[0];
    }

    return categoryId?.length > 0 ? parseInt(categoryId) : null;
  });

  const productId = computed(() => {
    let categoryId = window.location.hash.replace('#', '');
    let productId = null;

    if (categoryId.includes('-')) {
      const parts = categoryId.split('-');

      productId = parts[1];
    }

    return productId?.length > 0 ? parseInt(productId) : null;
  });

  const selectedMenu = computed(
    () => props.menus.find((m: Menu) => Number(m.id) === menuId.value)
  );

  const selectedCategory = computed(() => {
    if (!categoryId.value || !selectedMenu.value) {
      return null;
    }

    return selectedMenu.value.categories.find(
      (c: Category) => Number(c.id) === categoryId.value
    );
  });

  const selectedProduct = computed(() => {
    if (!productId.value || !selectedMenu.value) {
      return null;
    }

    return props.products?.find(
      (p: Product) => p.id === productId.value
    );
  });

  function onBackFromMenu() {
    router.visit(
      window.location.pathname.split('/menu/')[0],
      {
        fresh: false,
      }
    );
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
