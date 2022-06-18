<template>
    <div style="display: flex; justify-content: center; align-items: center;">
        <div class="marketplace">

            <VueHorizontal class="vue-horizontal menus" snap="center">
                <section class="menus-item" :class="{active: selections.menu === menu}"
                         @click="toggleMenu(menu)"
                         v-for="menu in menus.data">
                <span class="menus-item-text">
                    {{ menu.title }}
                </span>
                </section>
            </VueHorizontal>

            <VueHorizontal class="vue-horizontal categories" snap="center">
                <section class="categories-item"
                         :class="{active: selections.category === category}"
                         @click="toggleCategory(category)"
                         v-for="category in selections.menu.categories">
                    <img class="categories-item-img" :alt="category.title"
                         :src="category.media.length ? category.media[0].url : category.default_media[0].url"/>
                    <span class="categories-item-span">
                        {{ category.title }}
                    </span>
                </section>
            </VueHorizontal>

            <div class="filters">
                <div class="search">
                    <div>
                        <input class="search-input h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                               ref="search" type="text"
                               placeholder="Enter search string..."
                               @keyup.enter="applySearch(getSearch())"/>
                    </div>
                    <div style="margin-left: 4px">
                        <button class="search-button btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                                @click="applySearch(getSearch())">
                            search
                        </button>
                        <button class="search-button btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                                @click="applySearch('')">
                            x
                        </button>
                    </div>
                </div>
            </div>

            <div class="list flex-gap">
                <div class="list-col" v-for="items in columns">
                    <div class="list-item" v-for="item in items">
                        <img class="list-item-img" :alt="item.title"
                             :src="item.media.length ? item.media[0].url : item.default_media[0].url"/>
                        <div class="list-item-details">
                        <span class="list-item-title">
                            {{ item.title }}
                        </span>
                            <span class="list-item-description">
                            {{ item.description }}
                        </span>

                            <div class="list-item-info">
                            <span class="list-item-weight">
                                {{ item.weight }}g
                            </span>
                                <span class="list-item-price">
                                ${{ item.price }}
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="pagination" v-show="this.products && this.products.data && this.products.data.length">
                <div style="flex-grow: 1; flex-basis: 150px;">
<!--                    <button class="font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"-->
<!--                            @click="getProducts(products.meta.current_page)">-->
<!--                        ↻-->
<!--                    </button>-->
                </div>

                <div>
                    <button class="font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                            :disabled="products.meta.current_page <= 1"
                            @click="getProducts(1)">
                        «
                    </button>
                    <button class="font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                            :disabled="products.meta.current_page <= 1"
                            @click="getProducts(products.meta.current_page - 1)">
                        ‹
                    </button>
                    <span class="text-sm text-80 px-4 ml-auto">
                    {{ products.meta.current_page }}
                </span>
                    <button class="font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                            :disabled="products.meta.current_page >= products.meta.last_page"
                            @click="getProducts(products.meta.current_page + 1)">
                        ›
                    </button>

                    <button class="font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                            :disabled="products.meta.current_page >= products.meta.last_page"
                            @click="getProducts(products.meta.last_page)">
                        »
                    </button>
                </div>

                <div style="display: flex; justify-content: end; flex-grow: 1; flex-basis: 150px;">
                    <span v-if="products.data.length" class="text-sm text-80 px-4 ml-auto">
                        {{ products.meta.from }}-{{ products.meta.to }} of {{ products.meta.total }}
                    </span>
                </div>
            </nav>

            <div class="no-results" v-show="!this.products || !this.products.data || !this.products.data.length">
                <img class="no-results-img"
                     alt="No results"
                     src="/storage/media/defaults/dish.svg"/>
                <span class="no-results-text">
                    No results...
                </span>
            </div>

        </div>
    </div>
</template>

<script>
import VueHorizontal from 'vue-horizontal';

export default {
    components: {VueHorizontal},

    metaInfo() {
        return {
            title: 'Marketplace',
        }
    },
    data() {
        return {
            menus: [],
            products: [],
            columns: [],
            selections: {
                search: null,
                menu: null,
                category: null,
            }
        };
    },
    mounted() {
        this.getMenus();
    },
    methods: {
        getSearch() {
            return this.$refs && this.$refs.search ? this.$refs.search.value : null;
        },
        setSearch(search) {
            this.$refs.search.value = search;
        },
        getMenus() {
            Nova.request().get('/nova-vendor/marketplace/menus')
                .then(response => {
                    this.menus = response.data;

                    if (this.menus.data.length === 0) {
                        this.selections.menu = null;
                        this.selections.category = null;
                    } else {
                        this.toggleMenu(this.menus.data[0]);
                    }

                    this.getProducts();
                });
        },
        getProducts(page = 1, size = 10) {
            let url = '/nova-vendor/marketplace/products';
            let query = '?filter[menu_id]=' + this.selections.menu.id;

            if (this.selections.category) {
                const contains = this.selections.menu.categories.find(c => {
                        return this.selections.category.id === c.id
                    });

                if (contains) {
                    query += '&filter[categories]=' + this.selections.category.id ;
                }
            }
            if (this.selections.search) {
                query += '&filter[title]=' + this.selections.search ;
            }

            query += '&page[size]=' + size;
            query += '&page[number]=' + page;

            Nova.request().get(url + query)
                .then(response => {
                    this.products = response.data;
                    this.calculateColumns(this.products.data);
                });
        },
        applySearch(search) {
            if (this.selections.search === search) {
                return;
            }
            this.setSearch(search);

            this.selections.search = search;
            this.getProducts();
        },
        splitOnColumns(array, number) {
            const columns = [[], []]
            array.forEach((value, index) => {
                columns[index % number].push(value);
            });
            return columns;
        },
        calculateColumns(products) {
            this.columns = this.splitOnColumns(products, 2);
        },
        toggleMenu(menu) {
            if (this.selections.menu === menu) {
                return;
            }
            this.selections.menu = menu;
            this.getProducts();
        },
        toggleCategory(category) {
            if (this.selections.category === category) {
                category = null;
            }
            this.selections.category = category;
            this.getProducts();
        },
    }
}
</script>

<style>
.vue-horizontal {
    max-width: 100%;
    justify-content: center;
    align-items: center;
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
    padding: 16px 16px 16px 16px;

    color: #1D1D1B;
}

.menus {
    align-self: center;
    justify-self: center;
}

.menus-item {
    display: flex;
    align-items: center;
    margin-left: 16px;
    margin-right: 16px;
    padding: 8px 12px 8px 12px;
    border-radius: 4px;
    user-select: none;
}

.menus-item-text {
    font-style: normal;
    font-weight: 500;
    font-size: 24px;
    line-height: 28px;
    text-align: center;
}

.categories {
    align-self: center;
    justify-self: center;
    margin-top: 16px;
}

.categories-item {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    width: 88px;
    height: 88px;
    margin-left: 8px;
    margin-right: 8px;
    padding: 8px 12px 8px 12px;
    border-radius: 4px;
    text-align: center;
    user-select: none;
}

.categories-item-img {
    width: 72px;
    height: 48px;
}

.categories-item-span {
    align-self: center;
    justify-self: center;
    font-style: normal;
    font-weight: 400;
    font-size: 10px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.filters {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    margin-top: 16px;
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

    flex-basis: 180px;
    flex-grow: 1;
    padding: 8px 12px 8px 12px;
    border-radius: 4px;
    background-color: #eef1f4;
    font-style: normal;
    font-weight: 400;
    font-size: 14px;
    line-height: 16px;
    text-align: start;
    max-lines: 1;
}

.search-button {
    flex-basis: 60px;
    padding: 8px 12px 8px 12px;
    border-radius: 4px;
    background-color: #eef1f4;
    font-style: normal;
    font-weight: 400;
    font-size: 14px;
    line-height: 16px;
    text-align: center;
    max-lines: 1;
}

.list {
    display: flex;
    flex-wrap: wrap;
    gap: 64px;
    margin-top: 16px;
    padding: 0px 32px 0px 32px;
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
    gap: 4px;
    align-items: center;
    justify-content: space-between;
    padding: 8px;
    background: #FFFFFF;
    border-radius: 4px;
}

.list-item-img {
    width: 64px;
    height: 64px;
    user-select: none;
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
    height: 14px;
    display: block;
    font-style: normal;
    font-weight: 400;
    font-size: 12px;
    line-height: 14px;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
}

.list-item-description {
    height: 36px;
    display: block;
    max-lines: 3;
    line-clamp: 3;
    font-style: normal;
    font-weight: 300;
    font-size: 10px;
    line-height: 12px;
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
    font-size: 10px;
    line-height: 12px;
    text-align: start;
    overflow: hidden;
    text-overflow: ellipsis;
}

.list-item-price {
    display: block;
    flex-grow: 1;
    font-style: normal;
    font-weight: 400;
    font-size: 12px;
    line-height: 14px;
    text-align: end;
    overflow: hidden;
    text-overflow: ellipsis;
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
</style>
