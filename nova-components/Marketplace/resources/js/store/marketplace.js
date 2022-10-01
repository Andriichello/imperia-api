const tabs = [
  {
    title: 'Menus',
    target: 'products',
    image: '/images/dish.svg',
    /** from api **/
    menus: null,
    items: null,
    /** for display **/
    columns: [],
    /** filters **/
    filters: {
      menu: null,
      category: null,
      search: null,
    }
  },
  {
    title: 'Spaces',
    target: 'spaces',
    image: '/images/table.svg',
    /** from api **/
    categories: null,
    items: null,
    /** for display **/
    columns: [],
    /** filters **/
    filters: {
      category: null,
      search: null,
    }
  },
  {
    title: 'Tickets',
    target: 'tickets',
    image: '/images/ticket.svg',
    /** from api **/
    categories: null,
    items: null,
    /** for display **/
    columns: [],
    /** filters **/
    filters: {
      category: null,
      search: null,
    }
  },
  {
    title: 'Services',
    target: 'services',
    image: '/images/magic.svg',
    /** from api **/
    categories: null,
    items: null,
    /** for display **/
    columns: [],
    /** filters **/
    filters: {
      category: null,
      search: null,
    }
  },
];
const state = {
  tab: null, tabs
};

const getters = {
  getTab(state) {
    return state.tab;
  },
  getTabs(state) {
    return state.tabs;
  },
  getSearch(state) {
    return state.tab && state.tab.filters ? state.tab.filters.search : null;
  },
  getMenu(state) {
    return state.tab && state.tab.filters ? state.tab.filters.menu : null;
  },
  getMenus(state) {
    return state.tab ? state.tab.menus : null;
  },
  getItems(state) {
    return state.tab ? state.tab.items : null;
  },
  getCategory(state) {
    return state.tab && state.tab.filters ? state.tab.filters.category : null;
  },
  getCategories(state) {
    if (!state.tab) {
      return null;
    }

    return state.tab.filters && state.tab.filters.menu
      ? state.tab.filters.menu.categories : state.tab.categories;
  },
};

const actions = {
  /** Tabs **/
  selectTab({dispatch, commit, state}, tab) {
    commit('selectTab', tab);

    if (!tab.menus && tab.target === 'products') {
      dispatch('fetchMenus');
      return;
    }

    if (!tab.categories) {
      dispatch('fetchCategories', tab.target);
    }

    if (!tab.items) {
      dispatch('fetchItems', {target: tab.target});
    }
  },

  /** Menus **/
  selectMenu({dispatch, commit, state}, menu) {
    commit('selectMenu', menu);
    if (menu) {
      dispatch('fetchItems', {target: state.tab.target});
    }
  },
  fetchMenus({dispatch, commit}) {
    Nova.request().get('/nova-vendor/marketplace/menus')
      .then(response => {
        const menus = response.data.data;
        commit('setMenus', menus);
        dispatch('selectMenu', menus && menus.length ? menus[0] : null);
      });
  },

  /** Categories **/
  toggleCategory({dispatch, commit}, category) {
    commit('toggleCategory', category);
    dispatch('fetchItems', {target: state.tab.target});
  },
  fetchCategories({commit}, target) {
    Nova.request().get(`/nova-vendor/marketplace/categories?filter[target]=${target}`)
      .then(response => {
        commit('setCategories', {target, categories: response.data.data});
      });
  },

  /** Filters **/
  applySearch({dispatch, commit, state}, search) {
    commit('applySearch', search);
    dispatch('fetchItems', {target: state.tab.target});
  },

  /** Items **/
  fetchItems({commit}, {target, page = 1, size = 10}) {
    let query = `?page[size]=${size}&page[number]=${page}`;

    const tab = state.tabs.find((tab) => tab.target === target);
    if (tab.filters.menu) {
      query += `&filter[menus]=${tab.filters.menu.id}`
    }
    if (tab.filters.search) {
      query += `&filter[title]=${tab.filters.search}`
    }
    if (tab.filters.category) {
      if (!tab.filters.menu) {
        query += `&filter[categories]=${tab.filters.category.id}`;
      } else if (tab.filters.menu.categories.includes(tab.filters.category)) {
        query += `&filter[categories]=${tab.filters.category.id}`;
      }
    }

    Nova.request().get(`/nova-vendor/marketplace/${target}` + query)
      .then(response => {
        commit('setItems', {target, items: response.data});
      });
  },
};

const mutations = {
  /** Tabs **/
  selectTab(state, tab) {
    state.tab = tab;
  },

  /** Menus **/
  selectMenu(state, menu) {
    state.tab.filters.menu = menu;
  },
  setMenus(state, menus) {
    state.tabs = state.tabs.map((tab) => {
      if (tab.target !== 'products') {
        return tab;
      }

      return {...tab, menus};
    });

    if (state.tab && state.tab.target === 'products') {
      state.tab = state.tabs.find((tab) => tab.target === 'products');
    }
  },

  /** Categories **/
  toggleCategory(state, category) {
    const isSelected = state.tab.filters.category && category
      && state.tab.filters.category.id === category.id;

    state.tab.filters.category = isSelected ? null : category;
  },
  setCategories(state, {target, categories}) {
    state.tabs = state.tabs.map((tab) => {
      if (tab.target !== target) {
        return tab;
      }

      return {...tab, categories};
    });

    if (state.tab && state.tab.target === target) {
      state.tab = state.tabs.find((tab) => tab.target === target);
    }
  },

  /** Filters **/
  applySearch(state, search) {
    state.tab.filters.search = search;
  },

  /** Items **/
  setItems(state, {target, items}) {
    state.tabs = state.tabs.map((tab) => {
      if (tab.target !== target) {
        return tab;
      }

      return {...tab, items};
    });

    if (state.tab && state.tab.target === target) {
      state.tab = state.tabs.find((tab) => tab.target === target);
    }
  },
};

export default {
  state,
  getters,
  actions,
  mutations,
  namespaced: true,
};
