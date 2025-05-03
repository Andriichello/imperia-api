<script setup lang="ts">
  import {PropType, ref} from "vue";
  import {Category, Menu, Product, Restaurant} from "@/api";
  import {Splide, SplideSlide} from "@splidejs/vue-splide";

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

  const categoryProducts = (menu: Menu, category: Category) => {
    return menu.products!.filter((p: Product) => p.category_ids.includes(category.id))
  }

  // Switch to another menu
  const switchMenu = (menu: Menu) => {
    // Only update if it's a different menu
    if (menu.id !== selectedMenu.value.id) {
      const basePath = window.location.pathname.split('/menu/')[0];

      // Update the URL in the browser without page reload
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

  // Switch to another menu
  const switchCategory = (category: Category) => {
    // Only update if it's a different menu
    if (category.id !== selectedCategory.value?.id) {
      // Update the URL in the browser without page reload
      window.history.pushState(
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

    <div class="w-full flex flex-col px-3 py-3 gap-3">
      <template v-for="menu in menus" :key="menu.id">
        {{ void(isSelectedMenu = (menu.id === selectedMenu.id)) }}

        <div class="w-full flex flex-col text-center px-3 py-3 bg-base-200/40">
          <div class="w-full flex flex-col text-center px-3 py-3 rounded"
               :class="{'bg-warning/20': isSelectedMenu}"
               @click="switchMenu(menu)">
            <h3 class="text-xl font-bold">
              {{ menu.title }}
            </h3>
            <p>
              {{ menu.description }}
            </p>
          </div>

          <template v-for="category in menu.categories" :key="category.id" v-if="isSelectedMenu">
            {{ void(isSelectedCategory = (category.id === selectedCategory?.id)) }}

            <div class="w-full flex flex-col text-center py-3 bg-base-200/40 gap-3">
              <div class="w-full flex flex-col text-center px-3 py-3 rounded"
                   :class="{'bg-warning/20': isSelectedCategory}"
                   @click="switchCategory(category)"
                   :id="category.id">

                <h3 class="text-lg font-light underline">
                  {{ category.title }}
                </h3>
                <p>
                  {{ category.description }}
                </p>
              </div>

              {{ void(products = categoryProducts(menu, category)) }}

              <template v-if="!products.length">
                <div class="w-full flex flex-col text-center px-3 py-3 bg-base-200/40">
                  <div class="w-full flex flex-col text-center px-3 py-3 rounded">
                    <h3>Unfortunately, this category is empty</h3>
                  </div>
                </div>
              </template>

              <template v-for="product in products" :key="product.id" v-else>
                <div class="w-full flex flex-col text-center rounded shadow-lg">
                  <div class="w-full flex flex-col text-center rounded bg-base-100">
                    <template v-if="product.media?.length">
                      <Splide class="w-full h-35" :options="{
                            perPage: 1,
                            perMove: 1,
                            rewind: false,
                            rewindByDrag: false,
                            drag: Number(product.media!.length) > 1,
                            arrows: Number(product.media!.length) > 1,
                            pagination: true,
                          }">
                        <SplideSlide v-for="(media, index) in product.media ?? []" :key="media.id">
                          <img class="w-full h-35 object-cover object-center rounded-t"
                               :src="media.url" :alt="`Slide ${index}`"
                               :loading="index === 0 ? 'eager' : 'lazy'"/>
                        </SplideSlide>
                      </Splide>
                    </template>


                    <h3 class="text-lg font-light px-3 mt-1">
                      {{ product.title }}
                    </h3>
                    <p class="text-md font-light opacity-80 px-3 mb-3">
                      {{ product.description }}
                    </p>
                  </div>

                </div>
              </template>

            </div>
          </template>
        </div>
      </template>
    </div>
  </div>
</template>
