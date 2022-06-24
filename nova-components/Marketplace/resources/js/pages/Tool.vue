<template>
    <div style="display: flex; flex-direction: column; justify-content: start; align-items: center; min-height: 1000px">
        <div class="marketplace">
            <div class="tabs">
                <div class="tabs-item" :class="{selected: tab === t}"
                     @click="toggleTab(t)"
                     v-for="t in tabs">
                    <img class="tabs-item-img" :alt="t.title" :src="t.image"/>
                    <span class="tabs-item-text">
                        {{ t.title }}
                    </span>
                </div>
            </div>

            <div class="horizontal menus" v-if="tab && tab.menus">
                <section class="menus-item" :class="{active: tab.filters.menu === m}"
                         @click="toggleMenu(tab, m)"
                         v-for="m in tab.menus.data">
                <span class="menus-item-text">
                    {{ m.title }}
                </span>
                </section>
            </div>

            <div class="horizontal categories" v-if="tab && (tab.categories || (tab.filters.menu && tab.filters.menu.categories))">
                <section class="categories-item"
                         :class="{active: tab.filters.category === c}"
                         @click="toggleCategory(tab, c)"
                         v-for="c in tab.target === 'products' ? tab.filters.menu.categories : tab.categories.data">
                    <img class="categories-item-img" :alt="c.title"
                         :src="c.media && c.media.length ? c.media[0].url : tab.image"/>
                    <span class="categories-item-span">
                        {{ c.title }}
                    </span>
                </section>
            </div>

            <div class="filters">
                <div class="search">
                    <div>
                        <input class="search-input h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                               ref="search" type="text"
                               placeholder="Enter search string..."
                               :value="tab ? tab.filters.search : ''"
                               @keyup.enter="applySearch(tab, getSearch())"/>
                    </div>
                    <div style="margin-left: 4px">
                        <button class="search-button btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                                @click="applySearch(tab, getSearch())">
                            search
                        </button>
                        <button class="search-button btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                                @click="applySearch(tab,'')">
                            x
                        </button>
                    </div>
                </div>
            </div>

            <div class="list flex-gap" v-if="tab && tab.columns">
                <div class="list-col" v-for="column in tab.columns">
                    <div class="list-item" v-for="item in column">
                        <img class="list-item-img" :alt="item.title"
                             :src="item.media && item.media.length ? item.media[0].url : tab.image"/>
                        <div class="list-item-details">
                            <span class="list-item-title">
                                {{ item.title }}
                            </span>
                            <span class="list-item-description">
                                {{ item.description }}
                            </span>

                            <div class="list-item-info">
                                <span class="list-item-weight" v-show="item.weight">
                                    {{ item.weight < 1000 ? item.weight + 'g' : (item.weight / 1000.0).toFixed(2) + 'kg' }}
                                </span>
                                <span class="list-item-price" :class="{'list-item-price-centered': !item.weight}">
                                    {{ priceOf(item) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="pagination" v-if="tab && tab.items && tab.items.data && tab.items.data.length">
                <div style="flex-grow: 1; flex-basis: 150px;">
                    <button class="pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                            @click="fetchItems(tab, tab.items.meta.current_page)">
                        ↻
                    </button>
                </div>

                <div>
                    <button class="pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                            :disabled="tab.items.meta.current_page <= 1"
                            @click="fetchItems(tab,1)">
                        «
                    </button>
                    <button class="pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                            :disabled="tab.items.meta.current_page <= 1"
                            @click="fetchItems(tab, tab.items.meta.current_page - 1)">
                        ‹
                    </button>
                    <span class="text-sm text-80 px-4 ml-auto">
                    {{ tab.items.meta.current_page }}
                </span>
                    <button class="pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                            :disabled="tab.items.meta.current_page >= tab.items.meta.last_page"
                            @click="fetchItems(tab, tab.items.meta.current_page + 1)">
                        ›
                    </button>

                    <button class="pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                            :disabled="tab.items.meta.current_page >= tab.items.meta.last_page"
                            @click="fetchItems(tab, tab.items.meta.last_page)">
                        »
                    </button>
                </div>

                <div style="display: flex; justify-content: end; flex-grow: 1; flex-basis: 150px;">
                    <span v-if="tab.items.data.length" class="text-sm text-80 px-4 ml-auto">
                        {{ tab.items.meta.from }}-{{ tab.items.meta.to }} of {{ tab.items.meta.total }}
                    </span>
                </div>
            </nav>

            <div class="no-results" v-if="tab && tab.items && (!tab.items.data || !tab.items.data.length)">
                <img class="no-results-img"
                     alt="No results"
                     :src="tab.image"/>
                <span class="no-results-text">
                    No results...
                </span>
            </div>

        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            tab: {
                title: null,
                target: null,
                image: null,
                menu: null,
                items: null,
                columns: null,
                filters: {
                    menu: null,
                    category: null,
                    search: null,
                }
            },
            tabs: [
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
                }
            ],
        };
    },
    mounted() {
        this.toggleTab(this.tabs[0]);
    },
    methods: {
        getSearch() {
            return this.$refs && this.$refs.search ? this.$refs.search.value : null;
        },
        setSearch(search) {
            this.$refs.search.value = search;
        },
        priceOf(item) {
            if (!item) {
                return '';
            }

            if (item.price === 0) {
                return 'Free';
            } else if (item.price > 0) {
                return '$' + item.price;
            }

            let price = null;
            if (item.once_paid_price) {
                price = '$' + item.once_paid_price;
            }
            if (item.hourly_paid_price) {
                price = (price ? price + ' + ' : '') + '$' + item.hourly_paid_price + '/hour';
            }
            return price ?? '';
        },
        fetchMenus(tab) {
            Nova.request().get('/nova-vendor/marketplace/menus')
                .then(response => {
                    tab.menus = response.data;

                    if (tab.menus.data.length === 0) {
                        tab.filters.menu = null;
                        tab.filters.category = null;
                    } else {
                        this.toggleMenu(tab, tab.menus.data[0]);
                    }
                });
        },
        fetchItemsQuery(tab, page = 1, size = 10) {
            const url = `/nova-vendor/marketplace/${tab.target}`;
            let query = `?page[size]=${size}&page[number]=${page}`;

            if (tab.filters.menu) {
                query += `&filter[menu_id]=${tab.filters.menu.id}`
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

            return url + query;
        },
        fetchItems(tab, page = 1, size = 10) {
            Nova.request().get(this.fetchItemsQuery(tab, page, size))
                .then(response => {
                    tab.items = response.data;
                    this.calculateColumns(tab, tab.items.data);
                });
        },
        fetchCategoriesQuery(tab) {
            return `/nova-vendor/marketplace/categories?filter[target]=${tab.target}`;
        },
        fetchCategories(tab) {
            Nova.request().get(this.fetchCategoriesQuery(tab))
                .then(response => {
                    tab.categories = response.data;
                });
        },
        splitOnColumns(items, number) {
            const columns = [[], []]
            items.forEach((item, index) => {
                columns[index % number].push(item);
            });
            return columns;
        },
        calculateColumns(tab, items) {
            tab.columns = this.splitOnColumns(items, 2);
        },
        applySearch(tab, search) {
            if (tab.filters.search === search) {
                return;
            }

            tab.filters.search = search;
            this.fetchItems(tab);
        },
        toggleTab(tab) {
            console.log(tab.categories);

            if (this.tab === tab) {
                return;
            }

            this.tab = tab;
            if (tab.target === 'products') {
                if (!tab.menus || !tab.menus.data || !tab.menus.data.length) {
                    this.fetchMenus(tab);
                    return;
                }
            }

            if (tab.target !== 'products') {
                if (!tab.categories || !tab.categories.data || !tab.categories.data.length) {
                    this.fetchCategories(tab);
                }
            }

            if (!tab.items || !tab.items.data || !tab.items.length) {
                this.fetchItems(tab);
            }
        },
        toggleMenu(tab, menu) {
            if (tab.filters.menu === menu) {
                return;
            }
            tab.filters.menu = menu;
            if (menu) {
                tab.categories = menu.categories;
            }

            this.fetchItems(tab);
        },
        toggleCategory(tab, category) {
            if (tab.filters.category === category) {
                category = null;
            }
            tab.filters.category = category;
            this.fetchItems(tab);
        },
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

.tabs {
    width: 100%;
    max-width: 1000px;
    display: flex;
    flex-direction: row;
    justify-content: stretch;
    align-items: stretch;

    margin-top: 8px;
    margin-bottom: 16px;
}

.tabs-item {
    display: flex;
    flex-direction: row;
    flex-grow: 1;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 8px 16px 8px 16px;
    cursor: pointer;
    border-bottom: 4px solid white;
}

.tabs-item.selected {
    border-bottom: 4px solid #F3DA8D;
}

.tabs-item-img {
    width: 32px;
    height: 32px;
}

.tabs-item-text {
    font-style: normal;
    font-weight: 500;
    font-size: 22px;
    line-height: 25px;
    text-align: center;
    max-lines: 1;
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

.menus {
    display: flex;
    flex-direction: row;
    align-self: center;
    justify-self: center;
    margin-bottom: 16px;
}

.menus-item {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    margin-left: 16px;
    margin-right: 16px;
    padding: 8px 12px 8px 12px;
    border-radius: 4px;
    user-select: none;
    cursor: pointer;
}

.menus-item-text {
    font-style: normal;
    font-weight: 400;
    font-size: 22px;
    line-height: 25px;
    text-align: center;
}

.categories {
    display: flex;
    flex-direction: row;
    align-self: center;
    justify-self: center;
    margin-bottom: 16px;
}

.categories-item {;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex-shrink: 0;
    align-items: center;
    width: 110px;
    height: 110px;
    margin-left: 8px;
    margin-right: 8px;
    padding: 8px 8px 8px 8px;
    border-radius: 4px;
    text-align: center;
    user-select: none;
    cursor: pointer;
}

.categories-item-img {
    width: 94px;
    height: 70px;
}

.categories-item-span {
    height: 16px;
    align-self: center;
    justify-self: center;
    font-style: normal;
    font-weight: 400;
    font-size: 14px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.filters {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
}

.search {
    width: fit-content;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
}

.search-input {
    outline: none;
    border: none;
    background-image: none;
    -webkit-box-shadow: none;
    -moz-box-shadow: none;
    box-shadow: none;

    flex-basis: 80px;
    flex-grow: 1;
    padding: 8px 12px 8px 12px;
    margin-left: 4px;
    border-radius: 4px;
    background-color: #eef1f4;
    font-style: normal;
    font-weight: 400;
    font-size: 16px;
    line-height: 18px;
    text-align: start;
    max-lines: 1;
}

.search-button {
    flex-basis: 60px;
    padding: 8px 12px 8px 12px;
    margin-right: 4px;
    border-radius: 4px;
    background-color: #eef1f4;
    font-style: normal;
    font-weight: 400;
    font-size: 16px;
    line-height: 18px;
    text-align: center;
    max-lines: 1;
}

.list {
    display: flex;
    flex-wrap: wrap;
    gap: 64px;
    margin-top: 16px;
    padding: 0 32px 0 32px;
}

.list-col {
    flex-basis: 212px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    flex-wrap: wrap;
    justify-content: start;
    align-items: stretch;
    gap: 16px;
}

.list-item {
    height: 100px;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
    padding: 8px;
    background: #FFFFFF;
    border-radius: 4px;
}

.list-item-img {
    width: 72px;
    height: 72px;
    user-select: none;
    cursor: pointer;
}

.list-item-details {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    flex-wrap: wrap;
    gap: 4px;
    flex-basis: 128px;
    align-items: center;
    justify-content: space-between;
}

.list-item-title {
    height: 18px;
    display: block;
    font-style: normal;
    font-weight: 400;
    font-size: 16px;
    line-height: 18px;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
}

.list-item-description {
    height: 48px;
    display: block;
    max-lines: 3;
    line-clamp: 3;
    font-style: normal;
    font-weight: 300;
    font-size: 14px;
    line-height: 16px;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
}

.list-item-info {
    display: flex;
    flex-direction: row;
    align-self: stretch;
    justify-self: stretch;
    padding: 0 8px 0 8px;
}

.list-item-weight {
    display: block;
    flex-grow: 1;
    font-style: normal;
    font-weight: 300;
    font-size: 14px;
    line-height: 16px;
    text-align: start;
    overflow: hidden;
    text-overflow: ellipsis;
}

.list-item-price {
    display: block;
    flex-grow: 1;
    font-style: normal;
    font-weight: 400;
    font-size: 16px;
    line-height: 18px;
    text-align: end;
    overflow: hidden;
    text-overflow: ellipsis;
}

.list-item-price-centered {
    text-align: center;
}

.pagination {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    margin-top: 16px;
    background: #FFFFFF;
    border-radius: 4px;
}

.pagination-button {

}

.pagination-button:disabled,
.pagination-button[disabled]{
    opacity: 40%;
}

.no-results {
    display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 64px 64px 64px 64px;
}

.no-results-img {
    width: 100px;
    height: 100px;
}

.no-results-text {
    font-style: normal;
    font-weight: 500;
    font-size: 24px;
    line-height: 28px;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media screen and (max-width: 600px) {
    .tabs-item {
        align-items: center;
        justify-content: center;
        gap: 0;
    }

    .tabs-item-img {
        width: 32px;
        height: 32px;
        align-self: center;
        justify-self: center;
    }

    .tabs-item-text {
        width: 0;
        flex-grow: 0;
        visibility: collapse;
    }

    .search{
        width: auto;
    }

    .search-input {
        min-width: 100px;
        flex-grow: 1;
        flex-basis: 100px;
    }
}
</style>
