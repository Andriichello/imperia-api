<script setup lang="ts">
import {onMounted, onUnmounted, PropType, ref} from "vue";
import {Category, Menu, Restaurant} from "@/api";
import MenuInList from "@/Components/Menu/MenuInList.vue";
import CategoryNavBar from "@/Components/Menu/CategoryNavBar.vue";
import MenusDrawer from "@/Components/Menu/MenusDrawer.vue";
import {router} from "@inertiajs/vue3";

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

  const switchMenu = (menu: Menu) => {
    // Only update if it's a different menu
    if (menu.id !== selectedMenu.value.id) {
      const basePath = window.location.pathname.split('/menu/')[0];

      // Update the URL in the browser without a page reload
      router.visit(
        `${basePath}/menu/${menu.id}`,
        {replace: false}
      );

      ignoringScroll.value = true;

      // Update your component's state locally
      // This is needed since we're not hitting the backend
      // You'd need to track the selected menu ID locally
      selectedMenu.value = menu;

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

  const isDrawerOpen = ref(false)

  const ignoringScroll = ref(false);
  const ignoringScrollId = ref(0);

  const shouldNotScroll = ref(Date.now());

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
      const isBottomOutOfView = (group.offsetTop + group.clientHeight - 80) < scrollPosition;

      if (!isTopOutOfView || !isBottomOutOfView) {
        shouldNotScroll.value = Date.now();

        // select a category and scroll to it...
        switchCategory(category);
        break;
      }
    }
  };

  const scrollToCategory = (category: Category) => {
    const divider = document.getElementById('category-' + category.id);

    if ((shouldNotScroll.value === 0 || (Date.now() - shouldNotScroll.value) > 100) && divider) {
      ignoringScroll.value = true;

      console.log('scrolling to ' + category.title);
      window.scrollTo({
        top: divider.getBoundingClientRect().top + window.pageYOffset - 48,
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

  const onSwitchCategory = (category: Category) => {
    switchCategory(category);
    scrollToCategory(category);
  }

  onMounted(() => {
    console.log('onMounted...')

    window.addEventListener('scroll', onScroll);

    if (window.location.hash) {
      console.log('has hash', window.location.hash);
        const categoryId = window.location.hash.replace('#', '');
        const category = findCategory(categoryId);

        if (category) {
          console.log('found category', category.title);
          setTimeout(() => {
            switchCategory(category);

            if (selectedMenu.value.categories?.[0]?.id !== category.id) {
              console.log('scrolling to category');
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
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-start items-center bg-base-200/80 pb-50">
    <!-- Content -->
    <div class="w-full max-w-md flex flex-col justify-start items-center relative">
      <CategoryNavBar class="w-full sticky top-0 bg-base-100 z-10"
                      :categories="selectedMenu!.categories"
                      :selected="selectedCategory"
                      @switch-category="onSwitchCategory"
                      @open-drawer="isDrawerOpen = true"/>

      <MenusDrawer :open="isDrawerOpen"
                   :menus="menus"
                   :menu-id="selectedMenu.id"
                   :category-id="selectedCategory?.id"
                   @close="isDrawerOpen = false">
        <!-- Drawer content goes here -->
        <h2 class="text-lg font-bold mb-2">Drawer Title</h2>
        <p>This is your drawer content!</p>
      </MenusDrawer>

      <!-- Menus list -->
      <div class="w-full flex flex-col">
        <template v-for="menu in menus" :key="menu.id">
          <MenuInList :menu="menu"
                      :closed="menu.id !== selectedMenu?.id"
                      @switch-menu="switchMenu"
                      @switch-category="onSwitchCategory"/>
        </template>
      </div>
    </div>
  </div>
</template>
