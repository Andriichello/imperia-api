<script setup lang="ts">
  import {Category, Menu, Product} from "@/api";
  import CategoryInList from "@/Components/Menu/CategoryInList.vue";
  import {PropType} from "vue";
  import {Expand} from "lucide-vue-next";

  const emits = defineEmits(['switch-menu', 'switch-category']);

  const props = defineProps({
    menu: {
      type: Object as PropType<Menu>,
      required: true,
    },
    closed: {
      type: Boolean,
      default: false,
    },
    establishment: {
      type: String as PropType<string | null>,
      default: 'restaurant',
    }
  });

  const categoryProducts = (menu: Menu, category: Category) =>
    menu.products!.filter((p: Product) => p.category_ids.includes(category.id))

  const switchCategory = (category: Category) => {
    emits('switch-category', category);
  }
</script>W

<template>
  <div class="w-full flex flex-col">
    <div class="w-full flex flex-col">
      <div class="w-full flex flex-col text-center pb-0 px-3 cursor-pointer sticky"
           :class="{'pt-3': menu.description?.length > 0}"
           @click="emits('switch-menu', menu)">
        <p class="text-md font-light opacity-80"
           v-if="menu.description?.length > 0">
          {{ menu.description }}
        </p>
      </div>

      <template v-for="category in menu.categories" :key="category.id"
                v-if="!closed">
        <CategoryInList :category="category"
                        :products="categoryProducts(menu, category)"
                        :establishment="establishment"
                        @switch-category="switchCategory"/>

<!--        <div class="w-full h-[1px] bg-base-300"/>-->
      </template>
    </div>
  </div>
</template>
