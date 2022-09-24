<template>
  <div style="display: flex; flex-direction: column; justify-content: start; align-items: center; min-height: 1000px">
    <div class="marketplace">
      <Tabs :tab="tab" :tabs="tabs" v-if="tabs"
            @select-tab="onSelectTab"/>

      <Menus :menu="menu" :menus="menus" v-if="menus"
             @select-menu="onSelectMenu"/>

      <Categories :tab="tab" :category="category" :categories="categories" v-if="categories"
                  @toggle-category="onToggleCategory"/>

      <Filters/>

      <List :tab="tab" :items="items" v-if="items && items.data && items.data.length"/>
      <NoResults v-if="!items || !items.data || !items.data.length"/>
      <Pagination v-if="items" @load-page="onLoadPage"/>
    </div>
  </div>
</template>

<script>
import {mapActions, mapGetters} from 'vuex';

import Tabs from '../components/Tabs';
import Menus from '../components/Menus';
import Categories from '../components/Categories';
import Filters from '../components/Filters';
import List from '../components/List';
import Pagination from "../components/Pagination";
import NoResults from "../components/NoResults";

export default {
  components: {
    Tabs,
    Menus,
    Categories,
    Filters,
    List,
    Pagination,
    NoResults,
  },
  methods: {
    ...mapActions({
      selectTab: 'andriichello/marketplace/selectTab',
      selectMenu: 'andriichello/marketplace/selectMenu',
      toggleCategory: 'andriichello/marketplace/toggleCategory',

      fetchItems: "andriichello/marketplace/fetchItems",
      fetchMenus: "andriichello/marketplace/fetchMenus",
      fetchCategories: "andriichello/marketplace/fetchCategories",
    }),
    onSelectTab(tab) {
      this.selectTab(tab);
    },
    onSelectMenu(menu) {
      this.selectMenu(menu);
    },
    onToggleCategory(category) {
      this.toggleCategory(category);
    },
    onLoadPage(target, page) {
      this.fetchItems({target, page});
    },
  },
  computed: {
    ...mapGetters({
      tab: "andriichello/marketplace/getTab",
      tabs: "andriichello/marketplace/getTabs",
      menu: "andriichello/marketplace/getMenu",
      menus: "andriichello/marketplace/getMenus",
      category: "andriichello/marketplace/getCategory",
      categories: "andriichello/marketplace/getCategories",
      items: "andriichello/marketplace/getItems",
    })
  },
  created() {
    this.onSelectTab(this.tabs[0]);
  }
}
</script>

<style>
.horizontal {
  max-width: 100%;
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: center;
  align-self: center;
  justify-self: center;
  overflow-x: auto;
  -ms-overflow-style: none; /* for Internet Explorer, Edge */
  scrollbar-width: none; /* for Firefox */
}

.horizontal::-webkit-scrollbar {
  display: none; /* for Chrome, Safari, and Opera */
}

.active {
  background: #F3DA8D;
}

.non-active {
  background: #FFFFFF;
}

.marketplace {
  width: 100%;
  max-width: 1000px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: stretch;
  background-color: white;
  border-radius: 4px;
  padding: 0 16px 16px 16px;

  color: #1D1D1B;
}
</style>
