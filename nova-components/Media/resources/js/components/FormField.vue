<template>
  <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
    <template #field>
      <div>
        <!--                <input-->
        <!--                    :id="field.attribute"-->
        <!--                    type="text"-->
        <!--                    class="w-full form-control form-input form-input-bordered"-->
        <!--                    :class="errorClasses"-->
        <!--                    :placeholder="field.name"-->
        <!--                    v-model="value"-->
        <!--                />-->

        <div style=" display: flex; flex-wrap: wrap;
                     justify-content: stretch; align-items: start;
                     gap: 8px;">

          <div v-for="(item, index) in attached ?? []"
               style="display: flex; flex-direction: column;
                         flex-wrap: wrap; justify-content: stretch;
                         align-items: start; gap: 4px;">

            <div style="position: relative; width: 100px; height: 100px;">
              <img :alt="item.title ?? item.name" :src="item.url"
                   style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;
                                 background-image: linear-gradient(45deg, rgba(241, 245, 249, 0.5), rgba(100, 116, 139, 0.4));"/>

              <button type="button"
                      class="shadow relative bg-red-500 hover:bg-red-400 text-white cursor-pointer rounded text-sm font-bold inline-flex items-center justify-center shadow relative"
                      style="width: 20px; height: 20px; position: absolute; top: 4px; right: 4px; padding: 4px"
                      @click="removeItem(item)">
                x
              </button>

              <button type="button"
                      class="shadow relative bg-primary-500 hover:bg-primary-400 text-white cursor-pointer rounded text-sm font-bold inline-flex items-center justify-center shadow relative"
                      style="width: 20px; height: 20px; position: absolute; bottom: 4px; right: 28px; padding: 4px"
                      :disabled="index === 0"
                      @click="positionItem(item, index - 1)">
                ‹
              </button>

              <button type="button"
                      class="shadow relative bg-primary-500 hover:bg-primary-400 text-white cursor-pointer rounded text-sm font-bold inline-flex items-center justify-center shadow relative"
                      style="width: 20px; height: 20px; position: absolute; bottom: 4px; right: 4px; padding: 4px"
                      :disabled="index === attached.length - 1"
                      @click="positionItem(item, index + 1)">
                ›
              </button>
            </div>

            <span style="width: 100px; height: 16px;
                          font-style: normal; font-weight: 400; font-size: 14px; text-align: center;
                          max-lines: 1; overflow: hidden;text-overflow: ellipsis;">
                            {{ item.title ?? item.name }}
                        </span>

          </div>

        </div>

        <div class="media-form-actions" style="justify-content: start; align-items: center">
          <div class="media-form-actions-group">
                        <span class="media-form-link cursor-pointer"
                              style="width: fit-content; padding: 8px"
                              @click="showList ? closeList() : openList()">
                            {{ showList ? 'Close' : 'Open' }} Media
                        </span>

            <span class="media-form-link text-red-500 cursor-pointer"
                  style="width: fit-content; padding: 8px; margin-left: 16px"
                  v-show="attached && attached.length"
                  @click="clearItems()">
                            Clear
                        </span>
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px" v-if="showList">
          <div class="media-form-filters">
            <div class="media-form-filters-group">
              <div class="relative media-form-filters-input">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor"
                     width="20" height="24" class="inline-block absolute ml-2 text-gray-400"
                     style="top: 4px;">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>

                <input type="search" placeholder="Search by name"
                       class="appearance-none rounded-full h-8 pl-10 w-full bg-gray-100 dark:bg-gray-900 dark:focus:bg-gray-800 focus:bg-white focus:outline-none focus:ring focus:ring-primary-200 dark:focus:ring-gray-600"
                       aria-label="Search" aria-expanded="false" spellcheck="false"
                       v-model="filters.name" @keyup.enter="fetchItems()"
                       @keyup.esc="filters.name = ''; fetchItems()"
                       @keypress.enter.prevent>
              </div>
            </div>
          </div>

          <div class="media-form-list">
            <div class="media-form-list-item" v-for="item in items ? items.data : []"
                 @click="appendItem(item)">
              <img class="media-form-list-item-img"
                   :alt="item.title ?? item.name" :src="item.url"/>
              <span class="media-form-list-item-name">
                            {{ item.title ?? item.name }}
                    </span>
            </div>
          </div>

          <nav class="media-form-pagination" v-if="items && items.data && items.data.length">
            <div style="flex-grow: 1; flex-basis: 150px;">
              <button type="button"
                      class="media-form-pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                      @click="fetchItems(items.meta.current_page)">
                ↻
              </button>
            </div>

            <div>
              <button type="button"
                      class="media-form-pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                      :disabled="items.meta.current_page <= 1"
                      @click="fetchItems(1)">
                «
              </button>
              <button type="button"
                      class="media-form-pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                      :disabled="items.meta.current_page <= 1"
                      @click="fetchItems(items.meta.current_page - 1)">
                ‹
              </button>
              <span class="text-sm text-80 px-4 ml-auto">
                        {{ items.meta.current_page }}
                    </span>
              <button type="button"
                      class="media-form-pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                      :disabled="items.meta.current_page >= items.meta.last_page"
                      @click="fetchItems(items.meta.current_page + 1)">
                ›
              </button>

              <button type="button"
                      class="media-form-pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
                      :disabled="items.meta.current_page >= items.meta.last_page"
                      @click="fetchItems(items.meta.last_page)">
                »
              </button>
            </div>

            <div style="display: flex; justify-content: end; flex-grow: 1; flex-basis: 150px;">
                    <span v-if="items.data.length" class="text-sm text-80 px-4 ml-auto">
                        {{ items.meta.from }}-{{ items.meta.to }} of {{ items.meta.total }}
                    </span>
            </div>
          </nav>

          <div class="media-form-no-results" v-if="items && (!items.data || !items.data.length)">
                <span class="media-form-no-results-text">
                    No results...
                </span>
          </div>
        </div>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'

export default {
  mixins: [FormField, HandlesValidationErrors],
  props: ['resourceName', 'resourceId', 'field'],
  data() {
    const attached = [];
    this.field.value.forEach((a, i) => {
      a.order = i;
      attached.push(a);
    });

    return {
      items: null,
      showList: false,
      attached: attached,
      sorts: {
        createdAt: null,
        updatedAt: '-',
      },
      filters: {
        name: '',
      }
    };
  },
  methods: {
    /**
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.field.value ?? [];
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      const ids = this.attached.map(item => item.id);
      formData.append(this.field.attribute, ids.join(','));
    },

    openList() {
      this.fetchItems();

      this.showList = true;
    },

    closeList() {
      this.showList = false;
    },

    clearItems() {
      this.attached = [];
    },

    removeItem(item) {
      this.attached = this.attached.filter(i => {
        return i !== item;
      })
    },

    appendItem(item) {
      const found = this.attached.find(a => {
        return a.id === item.id;
      });

      if (found) {
        return;
      }

      item.order = this.attached.length;
      this.attached.push(item);
    },

    positionItem(item, position) {
      const result = [];

      this.attached.forEach(a => {
        if (a.id === item.id) {
          return;
        }

        if (a.order === position) {
          if (item.order > position) {
            result.push(item, a);
          } else {
            result.push(a, item);
          }
        } else {
          result.push(a);
        }
      });

      result.forEach((a, i) => {
        a.order = i;
      });

      this.attached = result;
    },

    fetchItemsQuery(page, size) {
      const url = `/nova-vendor/media/items`;
      let query = `?page[size]=${size}&page[number]=${page}`;

      const sorts = [];
      if (this.sorts.createdAt) {
        sorts.push(this.sorts.createdAt + 'created_at');
      }
      if (this.sorts.updatedAt) {
        sorts.push(this.sorts.updatedAt + 'updated_at');
      }
      if (sorts.length) {
        query += `&sort=` + sorts.join(',');
      }

      if (this.filters.name) {
        query += `&filter[name]=${this.filters.name}`
      }

      return url + query;
    },

    fetchItems(page = 1, size = 16) {
      Nova.request().get(this.fetchItemsQuery(page, size))
        .then(response => {
          this.items = response.data;
        });
    },

    applySearch(event) {
      event.preventDefault();

      this.fetchItems();
    },
  },
}
</script>

<style>

.media-form-link {
  font-style: normal;
  font-weight: 600;
  font-size: 16px;
}

.media-form-actions {
  width: 100%;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  padding: 8px 8px 8px 8px;
}

.media-form-actions-group {
  display: flex;
  flex-direction: row;
  justify-content: start;
  align-items: center;
  gap: 4px;
}

.media-form-filters {
  width: 100%;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  padding: 16px 8px 8px 8px;
}

.media-form-filters-input {
  min-width: 120px;
  max-width: 300px;
}

.media-form-filters-group {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 16px;
}

.media-form-list {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: stretch;
  align-items: start;
  gap: 8px;
  padding-bottom: 8px;
}

.media-form-list-item {
  display: flex;
  flex-direction: column;
  justify-content: start;
  align-items: center;
  gap: 2px;
}

.media-form-list-item-img {
  width: 100px;
  height: 100px;
  border-radius: 4px;
  object-fit: cover;
  background-image: linear-gradient(45deg, rgba(241, 245, 249, 0.5), rgba(100, 116, 139, 0.4));
}

.media-form-list-item-name {
  width: 100px;
  height: 16px;
  font-style: normal;
  font-weight: 400;
  font-size: 14px;
  text-align: center;
  max-lines: 1;
  overflow: hidden;
  text-overflow: ellipsis;
}

.media-form-pagination {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  margin-top: 16px;
  border-radius: 4px;
}

.media-form-pagination-button {

}

.media-form-pagination-button:disabled,
.media-form-pagination-button[disabled] {
  opacity: 40%;
}

.media-form-no-results {
  display: flex;
  flex-direction: column;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 48px 64px 64px 64px;
}

.media-form-no-results-text {
  font-style: normal;
  font-weight: 500;
  font-size: 24px;
  line-height: 28px;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
