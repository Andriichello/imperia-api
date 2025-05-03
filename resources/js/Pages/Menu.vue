<script setup lang="ts">
  import {PropType, ref} from "vue";
  import {Category, Menu, Restaurant} from "@/api";
  import MenuInList from "@/Components/Menu/MenuInList.vue";

  const props = defineProps({
    menuId: Number,
    restaurant: Object as PropType<Restaurant>,
    menus: Array as PropType<Menu[]>,
  });

  const selectedMenu = ref<Menu>(
    props.menus!.find((m: Menu) => Number(m.id) === props.menuId)
  );

  const findCategory = (categoryId: string|number|null) => {
    console.log(categoryId);

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

  const selectedCategory = ref<Category>(
    findCategory(window.location.hash?.replace('#', ''))
  );

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

  const switchCategory = (category: Category) => {
    // Only update if it's a different menu
    if (category.id !== selectedCategory.value?.id) {
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
</script>

<template>
  <div class="w-full h-full min-h-screen max-w-screen flex flex-col justify-start items-center bg-base-200/80 pb-20">
    <!-- Content -->
    <div class="w-full max-w-md flex flex-col justify-start items-center relative">
      <!-- Menus list -->
      <div class="w-full flex flex-col">
        <template v-for="menu in menus" :key="menu.id">
          <MenuInList :menu="menu"
                      @switch-menu="switchMenu"
                      @switch-category="switchCategory"/>
        </template>
      </div>

    </div>
  </div>
</template>
