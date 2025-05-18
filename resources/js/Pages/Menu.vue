<script setup lang="ts">
  import {onMounted, onUnmounted, PropType, ref, watch} from "vue";
  import {Category, Menu, Restaurant} from "@/api";
  import MenuInList from "@/Components/Menu/MenuInList.vue";
  import CategoryNavBar from "@/Components/Menu/CategoryNavBar.vue";
  import MenusDrawer from "@/Components/Menu/MenusDrawer.vue";
  import {router} from "@inertiajs/vue3";
  import MenuNavBar from "@/Components/Menu/MenuNavBar.vue";
  import NavBar from "@/Components/Menu/NavBar.vue";
  import BaseDrawer from "@/Components/Drawer/BaseDrawer.vue";
  import SearchDrawer from "@/Components/Menu/SearchDrawer.vue";
  import LanguagesDrawer from "@/Components/Menu/LanguagesDrawer.vue";

  const props = defineProps({
    menuId: {
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
    locale: {
      type: String,
      required: true,
    },
    supported_locales: {
      type: Array as PropType<string[]>,
      required: true,
    }
  });

  const selectedMenu = ref<Menu>(
    props.menus.find((m: Menu) => Number(m.id) === props.menuId)!
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

  const switchCategory = (category: Category) => {
    // Only update if it's a different menu
    if (category.id !== selectedCategory.value?.id) {
      // Update the URL in the browser without a page reload
      router.replace({
        url: window.location.pathname + '#' + category.id,
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

  const isMenusDrawerOpen = ref(false)
  const isSearchDrawerOpen = ref(false)
  const isLanguagesDrawerOpen = ref(false)

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

  const scrollToCategory = (category: Category) => {
    const divider = document.getElementById('category-' + category.id);

    if ((shouldNotScroll.value === 0 || (Date.now() - shouldNotScroll.value) > 100) && divider) {
      ignoringScroll.value = true;

      const stickyHeight = stickyRef.value?.clientHeight ?? 96;

      window.scrollTo({
        top: divider.getBoundingClientRect().top + window.pageYOffset - stickyHeight + 4,
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
    isMenusDrawerOpen.value = false;

    if (menu.id !== selectedMenu.value.id) {
      switchMenu(menu);
    }

    setTimeout(() => {
      switchCategory(category);
      scrollToCategory(category);
    }, 200);
  }

  const onSwitchMenu = (menu: Menu) => {
    isMenusDrawerOpen.value = false;

    switchMenu(menu, true);
  }

  const onBack = () => {
    router.visit(
      window.location.pathname.split('/menu/')[0],
      {
        fresh: false,
      }
    );
  }

  onMounted(() => {
    window.addEventListener('scroll', onScroll);

    if (window.location.hash) {
      const categoryId = window.location.hash.replace('#', '');
      const category = findCategory(categoryId);

      if (category) {
        setTimeout(() => {
          switchCategory(category);

          if (selectedMenu.value.categories?.[0]?.id !== category.id) {
            shouldNotScroll.value = 0;
            ignoringScroll.value = false;
            scrollToCategory(category);
          }
        }, 100);
      }
    }
  });

  onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
  });
</script>

<template>
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-start items-center bg-base-200/80 pb-[50vh]">
    <!-- Content -->
    <div class="w-full max-w-md flex flex-col justify-start items-center relative">
      <NavBar class="w-full px-2 py-2 bg-base-200"
              @on-back="onBack"
              @on-search="isSearchDrawerOpen = true"
              @on-language="isLanguagesDrawerOpen = true"/>

      <div class="w-full sticky top-0 bg-base-100 z-10 border-1 border-base-300"
           ref="stickyRef"
           :class="{'shadow-md': scrolledToSticky}">
        <MenuNavBar class="w-full"
                    :menus="menus"
                    :selected="selectedMenu"
                    @switch-menu="onSwitchMenu"
                    @open-drawer="isMenusDrawerOpen = true"/>

        <CategoryNavBar class="w-full"
                        :categories="selectedMenu!.categories"
                        :selected="selectedCategory"
                        @switch-category="onSwitchCategory"
                        @open-drawer="isMenusDrawerOpen = true"/>
      </div>

      <MenusDrawer :open="isMenusDrawerOpen"
                   :menus="menus"
                   :menu-id="selectedMenu.id"
                   :category-id="selectedCategory?.id"
                   @close="isMenusDrawerOpen = false"
                   @switch-menu="onSwitchMenu"
                   @switch-category="onSwitchCategory"/>

      <SearchDrawer :open="isSearchDrawerOpen"
                    @close="isSearchDrawerOpen = false"/>

      <LanguagesDrawer :open="isLanguagesDrawerOpen"
                       :locale="locale"
                       :supported_locales="supported_locales"
                       @close="isLanguagesDrawerOpen = false"/>

      <!-- Menus list -->
      <div class="w-full flex flex-col">
        <MenuInList :menu="selectedMenu"
                    :closed="false"
                    @switch-menu="switchMenu"
                    @switch-category="onSwitchCategory"/>
      </div>
    </div>
  </div>
</template>
