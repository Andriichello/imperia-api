<script setup lang="ts">
  import {onMounted, onUnmounted, PropType, ref, watch} from "vue";
  import {Category, Menu, Product, Restaurant} from "@/api";
  import MenuInList from "@/Components/Menu/MenuInList.vue";
  import CategoryNavBar from "@/Components/Menu/CategoryNavBar.vue";
  import {Deferred, router} from "@inertiajs/vue3";
  import MenuNavBar from "@/Components/Menu/MenuNavBar.vue";
  import { useI18n } from 'vue-i18n';

  const props = defineProps({
    menu_id: {
      type: Number,
      required: true,
    },
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
  });

  const emits = defineEmits(['open-drawer']);

  const i18n = useI18n();

  const selectedMenu = ref<Menu>(
    props.menus.find((m: Menu) => Number(m.id) === props.menu_id)!
  );

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

  const selectedCategory = ref<Category | null>(null);

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

  const onSwitchCategory = (category: Category, menu: Menu = selectedMenu.value) => {
    // isMenusDrawerOpen.value = false;
    //
    // if (menu.id !== selectedMenu.value.id) {
    //   switchMenu(menu);
    // }
    //
    // setTimeout(() => {
    //   switchCategory(category);
    //   scrollToCategory(category);
    // }, 200);
  }

  const onSwitchProduct = (product: Product, category: Category, menu: Menu = selectedMenu.value) => {
    // isMenusDrawerOpen.value = false;
    //
    // if (menu.id !== selectedMenu.value.id) {
    //   switchMenu(menu);
    // }
    //
    // setTimeout(() => {
    //   switchCategory(category, product);
    //   scrollToCategory(category, product);
    // }, 200);
  }

  const onSwitchMenu = (menu: Menu) => {
    // isMenusDrawerOpen.value = false;
    //
    // switchMenu(menu, true);
  }

  onMounted(() => {
    window.addEventListener('scroll', onScroll);

    if (!!props.products && window.location.hash) {
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
    }
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
</script>

<template>
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-start items-center bg-base-200/80 pb-[50vh]">
    <!-- Content -->
  </div>
</template>
