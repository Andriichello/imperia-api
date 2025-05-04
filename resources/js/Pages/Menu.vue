<script setup lang="ts">
import {onMounted, onUnmounted, PropType, ref, watch} from "vue";
  import {Category, Menu, Restaurant} from "@/api";
  import MenuInList from "@/Components/Menu/MenuInList.vue";
  import CategoryInList from "@/Components/Menu/CategoryInList.vue";
  import CategoryNavBar from "@/Components/Menu/CategoryNavBar.vue";
import categories from "../../../nova-components/Marketplace/resources/js/components/Categories.vue";

  const props = defineProps({
    menuId: Number,
    restaurant: Object as PropType<Restaurant>,
    menus: Array as PropType<Menu[]>,
  });

  const selectedMenu = ref<Menu>(
    props.menus!.find((m: Menu) => Number(m.id) === props.menuId)
  );

  const findCategory = (categoryId: string|number|null) => {
    if (categoryId !== null) {
      for (const menu: Menu of props.menus) {
        for (const category: Category of menu.categories) {
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

  const switchMenu = (menu: Menu) => {
    // Only update if it's a different menu
    if (menu.id !== selectedMenu.value.id) {
      const basePath = window.location.pathname.split('/menu/')[0];

      // Update the URL in the browser without a page reload
      window.history.pushState(
        {menuId: menu.id}, // state object
        '', // title (ignored by most browsers)
        `${basePath}/menu/${menu.id}` // URL
      );

      // Update your component's state locally
      // This is needed since we're not hitting the backend
      // You'd need to track the selected menu ID locally
      selectedMenu.value = menu;
    }
  };

  const switchCategory = (category: Category, force: bool = false) => {
    // Only update if it's a different menu
    if (force || category.id !== selectedCategory.value?.id) {
      // Update the URL in the browser without a page reload
      window.history.replaceState(
        {menuId: selectedMenu.id}, // state object
        '', // title (ignored by most browsers)
        `${window.location.pathname}#${category.id}` // URL
      );

      // Update your component's state locally
      // This is needed since we're not hitting the backend
      // You'd need to track the selected menu ID locally
      selectedCategory.value = category;
    }
  };

  const ignoringScroll = ref(false);
  const ignoringScrollId = ref(0);

  const shouldNotScroll = ref(null);

  const onScroll = () => {
    // Get the current scroll position
    const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

    // Because of momentum scrolling on mobiles, we shouldn't continue if it is less than zero
    if (scrollPosition < 0) {
      return;
    }

    if (ignoringScroll.value) {
      return;
    }

    const categoriesCount = selectedMenu.value.categories.length;

    for (let i = 0; i < categoriesCount; i++) {
      const category = selectedMenu.value.categories[i];
      const group = document.getElementById(`category-${category.id}`);

      if (!group) {
        continue;
      }

      const isTopOutOfView = group.offsetTop < scrollPosition
      const isBottomOutOfView = (group.offsetTop + group.clientHeight - 60) < scrollPosition;

      if (!isTopOutOfView || !isBottomOutOfView) {
        shouldNotScroll.value = Date.now();

        // select a category and scroll to it...
        switchCategory(category);
        break;
      }
    }
  };

  watch(() => selectedCategory.value, (newCategory) => {
    if (newCategory) {
      const divider = document.getElementById('category-' + newCategory.id);

      if ((shouldNotScroll.value === 0 || (Date.now() - shouldNotScroll.value) > 100) && divider) {
        ignoringScroll.value = true;

        window.scrollTo({
          top: divider.getBoundingClientRect().top + window.pageYOffset - (window.innerHeight >= 800 ? 92 : 48),
          behavior: 'smooth'
        });

        const idToCheck = ignoringScrollId.value++;

        setTimeout(() => {
          if (idToCheck === (ignoringScrollId.value - 1)) {
            ignoringScroll.value = false;
          }
        }, 1000)
      }
    }

    shouldNotScroll.value = 0;
  });

  onMounted(() => {
    window.addEventListener('scroll', onScroll);

    if (window.location.hash) {
      setTimeout(
        () => {
          const categoryId = window.location.hash.replace('#', '');
          const category = findCategory(categoryId);

          if (category && selectedMenu.value.categories?.[0]?.id !== category.id) {
            switchCategory(category, true);
          }
        },
        200
      )
    }
  });

  onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
  })
</script>

<template>
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-start items-center bg-base-200/80 pb-20">
    <!-- Content -->
    <div class="w-full max-w-md flex flex-col justify-start items-center relative">
      <CategoryNavBar class="w-full sticky top-0 bg-base-100 z-10"
                      :categories="selectedMenu!.categories"
                      :selected="selectedCategory"
                      @switch-category="switchCategory"/>

      <!-- Menus list -->
      <div class="w-full flex flex-col">
        <template v-for="menu in menus" :key="menu.id">
          <MenuInList :menu="menu"
                      :closed="menu.id !== selectedMenu?.id"
                      @switch-menu="switchMenu"
                      @switch-category="switchCategory"/>
        </template>
      </div>

    </div>
  </div>
</template>
