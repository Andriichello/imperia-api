<template>
    <div>
        <div class="marketplace">

            <VueHorizontal class="vue-horizontal menus" snap="center">
                <section class="menus-item" :class="{active: selections.menu === menu}"
                         @click="toggleMenu(menu)"
                         v-for="menu in menus">
                    <span class="menus-item-text">
                        {{ menu.title }}
                    </span>
                </section>
            </VueHorizontal>

            <VueHorizontal class="vue-horizontal categories" snap="center">
                <section class="categories-item" :class="{active: selections.category === category}"
                         @click="toggleCategory(category)"
                         v-for="category in selections.menu.categories">
                    <img class="categories-item-img" :alt="category.title"
                         :src="category.media.length ? category.media[0].url : category.default_media[0].url"/>
                    <span class="categories-item-span">
                        {{ category.title }}
                    </span>
                </section>
            </VueHorizontal>

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
                menu: null,
                category: null,
            }
        };
    },
    mounted() {
        this.getMenus();
    },
    methods: {
        getMenus() {
            Nova.request().get('/nova-vendor/marketplace/menus')
                .then(response => {
                    this.menus = response.data.menus;

                    if (this.menus.length === 0) {
                        this.selections.menu = null;
                        this.selections.category = null;
                    } else {
                        this.toggleMenu(this.menus[0]);
                    }

                    this.getProducts();
                });
        },
        getProducts() {
            let url = '/nova-vendor/marketplace/products';
            let query = '?menu_id=' + this.selections.menu.id;

            if (this.selections.category) {
                query += '&category_id=' + this.selections.category.id;
            }

            Nova.request().get(url + query)
                .then(response => {
                    this.products = response.data.products;
                    this.calculateColumns(this.products);
                });
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
    flex-wrap: wrap;
}

.active {
    background: #F3DA8D;
}

.non-active {
    background: #FFFFFF;
}

.marketplace {
    padding: 12px 42px 12px 42px;
}

.menus {
    align-self: center;
    justify-self: center;
    padding: 6px 8px 6px 8px;
}

.menus-item {
    display: flex;
    align-items: center;
    margin-right: 36px;
    padding: 8px 12px 8px 12px;
    border-radius: 4px;
}

.menus-item-text {
    font-style: normal;
    font-weight: 500;
    font-size: 24px;
    line-height: 28px;
    text-align: center;
}

.categories {
    justify-content: center;
    align-items: center;
    padding: 6px 8px 6px 8px;
}

.categories-item {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    width: 88px;
    height: 88px;
    margin-right: 12px;
    padding: 8px 12px 8px 12px;
    border-radius: 4px;
    text-align: center;
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

.list {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 16px;
    padding: 0 8px 0 8px;
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
</style>
