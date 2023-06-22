<template>
  <div>
    <Head title="Зображення"/>

    <Heading class="mb-6">Зображення</Heading>

    <Card class="media-card" v-if="!view.item">
      <div class="media-actions">
        <div class="media-actions-group">
          <button
            class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"
            @click="toggleMode()">
            {{ selections.mode === 'select' ? 'Режим вибору' : 'Режим перегляду' }}
          </button>
<!--          <button-->
<!--            class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"-->
<!--            v-show="selections.mode === 'select'"-->
<!--            @click="toggleSelectAll()">-->
<!--            Обрати всі-->
<!--          </button>-->
          <button
            class="shadow relative bg-red-500 hover:bg-red-400 text-white cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600 inline-flex items-center justify-center h-9 px-3 shadow relative bg-red-500 hover:bg-red-400 text-white"
            v-show="selections.items.length"
            @click="deleteFiles()">
            Видалити {{ '(' + selections.items.length + ')' }}
          </button>
        </div>

        <label
          class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0">
          <input id="upload-file-input" type="file" accept="image/*"
                 hidden multiple @change="uploadFiles"/>
          Нове зображення
        </label>

        <!--                <button-->
        <!--                    class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0">-->
        <!--                    Upload-->
        <!--                </button>-->
      </div>

      <div class="media-filters">
        <div class="media-filters-group">
          <div class="relative media-filters-input">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                 width="20" height="24" class="inline-block absolute ml-2 text-gray-400" style="top: 4px;">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="search" placeholder="Пошук"
                   class="appearance-none rounded-full h-8 pl-10 w-full bg-gray-100 dark:bg-gray-900 dark:focus:bg-gray-800 focus:bg-white focus:outline-none focus:ring focus:ring-primary-200 dark:focus:ring-gray-600"
                   aria-label="Search" aria-expanded="false" spellcheck="false"
                   v-model="searchFilter" @keypress.enter.prevent>
          </div>

          <!--                    <div class="relative media-filters-input">-->
          <!--                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"-->
          <!--                             width="20" height="24" class="inline-block absolute ml-2 text-gray-400" style="top: 4px;">-->
          <!--                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"-->
          <!--                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>-->
          <!--                        </svg>-->
          <!--                        <input type="search" placeholder="By folder"-->
          <!--                               class="appearance-none rounded-full h-8 pl-10 w-full bg-gray-100 dark:bg-gray-900 dark:focus:bg-gray-800 focus:bg-white focus:outline-none focus:ring focus:ring-primary-200 dark:focus:ring-gray-600"-->
          <!--                               aria-label="Search" aria-expanded="false" spellcheck="false"-->
          <!--                               v-model="filters.folder" @keyup.enter="fetchItems()"-->
          <!--                               @keyup.esc="filters.folder = ''; fetchItems()">-->
          <!--                    </div>-->

          <!--                    <div class="relative media-filters-input">-->
          <!--                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"-->
          <!--                             width="20" height="24" class="inline-block absolute ml-2 text-gray-400" style="top: 4px;">-->
          <!--                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"-->
          <!--                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>-->
          <!--                        </svg>-->
          <!--                        <input type="search" placeholder="By disk"-->
          <!--                               class="appearance-none rounded-full h-8 pl-10 w-full bg-gray-100 dark:bg-gray-900 dark:focus:bg-gray-800 focus:bg-white focus:outline-none focus:ring focus:ring-primary-200 dark:focus:ring-gray-600"-->
          <!--                               aria-label="Search" aria-expanded="false" spellcheck="false"-->
          <!--                               v-model="filters.disk" @keyup.enter="fetchItems()"-->
          <!--                               @keyup.esc="filters.disk = ''; fetchItems()">-->
          <!--                    </div>-->
        </div>

        <!--                <button-->
        <!--                    class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"-->
        <!--                    :disabled="true"-->
        <!--                    @click="toggleDisplay()">-->
        <!--                    {{ selections.display === 'gallery' ? 'List' : 'Gallery' }}-->
        <!--                </button>-->
      </div>

      <div class="media-list">
        <div class="media-list-item" v-for="item in items ? items.data : []"
             @click="selections.mode === 'view' ? viewItem(item) : toggleSelect(item)">
          <img class="media-list-item-img" :class="{'media-list-selection': selections.items.includes(item)}"
               :alt="item.title ?? item.name" :src="item.url"/>
          <span class="media-list-item-name">
                            {{ item.title ?? item.name }}
                    </span>
        </div>
      </div>

      <nav class="media-pagination" v-if="items && items.data && items.data.length">
        <div style="flex-grow: 1; flex-basis: 150px;">
          <button
            class="media-pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
            @click="fetchItems(items.meta.current_page)">
            ↻
          </button>
        </div>

        <div>
          <button
            class="media-pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
            :disabled="items.meta.current_page <= 1"
            @click="fetchItems(1)">
            «
          </button>
          <button
            class="media-pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
            :disabled="items.meta.current_page <= 1"
            @click="fetchItems(items.meta.current_page - 1)">
            ‹
          </button>
          <span class="text-sm text-80 px-4 ml-auto">
                        {{ items.meta.current_page }}
                    </span>
          <button
            class="media-pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
            :disabled="items.meta.current_page >= items.meta.last_page"
            @click="fetchItems(items.meta.current_page + 1)">
            ›
          </button>

          <button
            class="media-pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
            :disabled="items.meta.current_page >= items.meta.last_page"
            @click="fetchItems(items.meta.last_page)">
            »
          </button>
        </div>

        <div style="display: flex; justify-content: end; flex-grow: 1; flex-basis: 150px;">
                    <span v-if="items.data.length" class="text-sm text-80 px-4 ml-auto">
                        {{ items.meta.from }}-{{ items.meta.to }} з {{ items.meta.total }}
                    </span>
        </div>
      </nav>

      <div class="media-no-results" v-if="items && (!items.data || !items.data.length)">
                <span class="media-no-results-text">
                    Список порожній...
                </span>
      </div>
    </Card>

    <Card class="media-view-card" v-else>
      <div class="media-actions">
        <div class="media-actions-group">
          <button
            class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"
            @click="closeItem()">
            Назад
          </button>
        </div>

        <div class="media-actions-group">
          <label
            class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0">
            <input id="replace-file-input" type="file" accept="image/*"
                   hidden @change="replaceFiles"/>
            Замінити на іншим
          </label>
        </div>
      </div>

      <div class="media-view-content">
        <div class="media-view-img-div">
          <img class="media-view-img" v-if="view.item.extension !== 'svg'"
               :alt="view.item.title ?? view.item.name" :src="view.item.url"/>
          <object class="media-view-img" style="min-width: 100px; min-height: 100px" v-else
                  :data="view.item.url" type="image/svg+xml"/>
        </div>

        <div class="media-view-details">
          <div class="field-wrapper flex flex-col border-b border-gray-100 dark:border-gray-700 md:flex-row">
            <div class="px-6 md:px-8 mt-2 md:mt-0 w-full md:w-1/5 md:py-5">
              <label class="inline-block pt-2 leading-tight" for="item-id">
                ID
              </label>
            </div>
            <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 w-full md:w-3/5 md:py-5">
              <input class="w-full form-control form-input form-input-bordered" disabled
                     id="item-id" type="text" placeholder="ID" v-model="view.item.id">
            </div>
          </div>

          <div class="field-wrapper flex flex-col border-b border-gray-100 dark:border-gray-700 md:flex-row">
            <div class="px-6 md:px-8 mt-2 md:mt-0 w-full md:w-1/5 md:py-5">
              <label class="inline-block pt-2 leading-tight" for="item-name">
                Назва файлу
              </label>
            </div>
            <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 w-full md:w-3/5 md:py-5">
              <input class="w-full form-control form-input form-input-bordered"
                     id="item-name" type="text" placeholder="Name" v-model="update.name">
            </div>
          </div>

          <div class="field-wrapper flex flex-col border-b border-gray-100 dark:border-gray-700 md:flex-row">
            <div class="px-6 md:px-8 mt-2 md:mt-0 w-full md:w-1/5 md:py-5">
              <label class="inline-block pt-2 leading-tight" for="item-title">
                Назва зображення
              </label>
            </div>
            <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 w-full md:w-3/5 md:py-5">
              <input class="w-full form-control form-input form-input-bordered"
                     id="item-title" type="text" placeholder="Title" v-model="update.title">
            </div>
          </div>

          <div class="field-wrapper flex flex-col border-b border-gray-100 dark:border-gray-700 md:flex-row">
            <div class="px-6 md:px-8 mt-2 md:mt-0 w-full md:w-1/5 md:py-5">
              <label class="inline-block pt-2 leading-tight" for="item-description">
                Опис
              </label>
            </div>
            <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 w-full md:w-3/5 md:py-5">
              <input class="w-full form-control form-input form-input-bordered"
                     id="item-description" type="text" placeholder="Description" v-model="update.description">
            </div>
          </div>

          <div class="field-wrapper flex flex-col border-b border-gray-100 dark:border-gray-700 md:flex-row">
            <div class="px-6 md:px-8 mt-2 md:mt-0 w-full md:w-1/5 md:py-5">
              <label class="inline-block pt-2 leading-tight" for="item-extension">
                Розширення файлу
              </label>
            </div>
            <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 w-full md:w-3/5 md:py-5">
              <input class="w-full form-control form-input form-input-bordered" disabled
                     id="item-extension" type="text" placeholder="Extension" v-model="view.item.extension">
            </div>
          </div>

          <div class="field-wrapper flex flex-col border-b border-gray-100 dark:border-gray-700 md:flex-row">
            <div class="px-6 md:px-8 mt-2 md:mt-0 w-full md:w-1/5 md:py-5">
              <label class="inline-block pt-2 leading-tight" for="item-disk">
                Диск
              </label>
            </div>
            <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 w-full md:w-3/5 md:py-5">
              <input class="w-full form-control form-input form-input-bordered" disabled
                     id="item-disk" type="text" placeholder="Disk" v-model="view.item.disk">
            </div>
          </div>

          <div class="field-wrapper flex flex-col border-b border-gray-100 dark:border-gray-700 md:flex-row">
            <div class="px-6 md:px-8 mt-2 md:mt-0 w-full md:w-1/5 md:py-5">
              <label class="inline-block pt-2 leading-tight" for="item-disk">
                Папка
              </label>
            </div>
            <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 w-full md:w-3/5 md:py-5">
              <input class="w-full form-control form-input form-input-bordered" disabled
                     id="item-folder" type="text" placeholder="Folder" v-model="view.item.folder">
            </div>
          </div>

          <div class="field-wrapper flex flex-col border-b border-gray-100 dark:border-gray-700 md:flex-row">
            <div class="px-6 md:px-8 mt-2 md:mt-0 w-full md:w-1/5 md:py-5">
              <label class="media-view-link inline-block pt-2 leading-tight" for="item-url">
                <a id="item-url" :href="view.item.url" target="_blank">
                  Посилання
                </a>
              </label>
            </div>
            <div class="mt-1 md:mt-0 pb-5 px-6 md:px-8 w-full md:w-3/5 md:py-5">
              <input class="w-full form-control form-input form-input-bordered" disabled
                     id="item-url" type="text" placeholder="Url" v-model="view.item.url">
            </div>
          </div>

        </div>
      </div>

      <div class="media-actions">
        <div class="media-actions-group"></div>

        <div class="media-actions-group">
          <button
            class="shadow relative bg-red-500 hover:bg-red-400 text-white cursor-pointer rounded text-sm font-bold focus:outline-none focus:ring ring-primary-200 dark:ring-gray-600 inline-flex items-center justify-center h-9 px-3 shadow relative bg-red-500 hover:bg-red-400 text-white"
            @click="deleteItem()">
            Видалити
          </button>
          <button
            class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"
            @click="updateItem()">
            Оновити
          </button>
        </div>
      </div>
    </Card>

  </div>
</template>

<script>
import Card from '../../../../../vendor/laravel/nova/resources/js/components/Card'
import {debounce} from "lodash";

export default {
  components: {
    Card,
  },
  data() {
    return {
      items: null,
      update: {
        loading: false,
        name: '',
        title: '',
        description: '',
      },
      replace: {
        loading: false,
        total: 0,
        done: 0,
      },
      upload: {
        loading: false,
        total: 0,
        done: 0,
      },
      delete: {
        loading: false,
        items: [],
        total: 0,
        done: 0,
      },
      sorts: {
        createdAt: null,
        updatedAt: '-',
      },
      searchFilter: '',
      filters: {
        name: '',
        extension: '',
        disk: '',
        folder: '',
      },
      view: {
        item: null,
      },
      selections: {
        mode: 'view',
        items: [],
        display: 'list',
      },
    };
  },
  mounted() {
    this.fetchItems();
  },
  watch: {
    searchFilter: debounce(
      function (newVal) {
        this.fetchItems();
      }, 350
    ),
  },
  methods: {
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

      if (this.searchFilter && this.searchFilter.length) {
        query += `&filter[search]=${this.searchFilter}`
      }
      if (this.filters.name) {
        query += `&filter[name]=${this.filters.name}`
      }
      if (this.filters.extension) {
        query += `&filter[extension]=${this.filters.extension}`
      }
      if (this.filters.disk) {
        query += `&filter[disk]=${this.filters.disk}`
      }
      if (this.filters.folder) {
        query += `&filter[folder]=${this.filters.folder}`
      }

      return url + query;
    },
    fetchItems(page = 1, size = 24) {
      Nova.request().get(this.fetchItemsQuery(page, size))
        .then(response => {
          this.items = response.data;

          if (this.view.item && this.items.data) {
            this.items.data.forEach(item => {
              if (this.view.item.id === item.id) {
                this.viewItem(item);
              }
            });
          }
        });
    },

    toggleMode() {
      this.view.item = null;
      this.selections.items = [];

      if (this.selections.mode === 'select') {
        this.selections.mode = 'view';
      } else if (this.selections.mode === 'view') {
        this.selections.mode = 'select';
      }
    },
    toggleDisplay() {
      if (this.selections.display === 'list') {
        this.selections.display = 'gallery';
      } else if (this.selections.display === 'gallery') {
        this.selections.display = 'list';
      }
    },

    toggleSelect(item) {
      if (this.selections.items.includes(item)) {
        this.selections.items = this.selections.items.filter(i => {
          return i !== item;
        });
      } else {
        this.selections.items.push(item);
      }
    },
    toggleSelectAll() {
      if (this.selections.items.length === (this.items && this.items.data ? this.items.data.length : 0)) {
        this.selections.items = [];
      } else {
        this.selections.items = this.items.data;
      }
    },

    viewItem(item) {
      this.view.item = item;

      this.update.name = item.name ? item.name : '';
      this.update.title = item.title ? item.title : '';
      this.update.description = item.description ? item.description : '';
    },
    closeItem() {
      this.view.item = null;
    },
    deleteItem() {
      if (!confirm('Видалити зобреження?')) {
        return;
      }

      this.delete.done = 0;
      this.delete.total = 1;
      this.delete.items = [this.view.item];

      this.deleteFile(0);
    },
    updateItem() {
      const data = new FormData();
      data.append('name', this.update.name);
      data.append('title', this.update.title);
      data.append('description', this.update.description);

      Nova.request().post('/nova-vendor/media/items/' + this.view.item.id + '?_method=PATCH', data)
        .then(r => {
          this.update.lodaing = false;

          this.fetchItems();

          if (r.data.message) {
            Nova.$toasted.show(r.data.message);
          }
        })
        .catch(e => {
          Nova.$toasted.error(e);
        });
    },

    replaceFinish() {
      this.replace.loading = false;
      Nova.$toasted.success(`Замінено: ${this.replace.done}/${this.replace.total}`);

      this.fetchItems();
    },
    replaceFile(index) {
      let file = this.files[index];

      if (!file) {
        return this.replaceFinish();
      }

      let config = {headers: {'Content-Type': 'multipart/form-data'}};
      let data = new FormData();
      data.append('file', file);
      data.append('name', file.name);

      Nova.request().post('/nova-vendor/media/items/' + this.view.item.id + '?_method=PATCH', data, config)
        .then(r => {
          this.replace.done++;

          if (r.data.message) {
            Nova.$toasted.show(r.data.message);
          }

          this.replaceFile(index + 1);
        })
        .catch(e => {
          let message = e;

          if (e.response && e.response.data) {
            if (e.response.data.message) {
              message = e.response.data.message;
            }
          }

          Nova.$toasted.error(message);

          this.replaceFile(index + 1);
        });
    },
    replaceFiles(input) {
      if (!input.target.files.length) {
        return;
      }

      this.replace.done = 0;
      this.replace.total = input.target.files.length;
      this.replace.loading = true;

      this.files = Object.assign({}, input.target.files);
      this.replaceFile(0);

      document.getElementById('replace-file-input').value = null;
    },

    uploadFinish() {
      this.upload.loading = false;
      Nova.$toasted.success(`Завантажено: ${this.upload.done}/${this.upload.total}`);

      this.fetchItems();
    },
    uploadFile(index) {
      let file = this.files[index];

      if (!file) {
        return this.uploadFinish();
      }

      let config = {headers: {'Content-Type': 'multipart/form-data'}};
      let data = new FormData();
      data.append('file', file);
      data.append('name', file.name);

      Nova.request().post('/nova-vendor/media/items', data, config)
        .then(r => {
          this.upload.done++;

          if (r.data.message) {
            Nova.$toasted.show(r.data.message);
          }

          this.uploadFile(index + 1);
        })
        .catch(e => {
          let message = e;

          if (e.response && e.response.data) {
            if (e.response.data.message) {
              message = e.response.data.message;
            }
          }

          Nova.$toasted.error(message);

          this.uploadFile(index + 1);
        });
    },
    uploadFiles(input) {
      if (!input.target.files.length) {
        return;
      }

      this.upload.done = 0;
      this.upload.total = input.target.files.length;
      this.upload.loading = true;

      this.files = Object.assign({}, input.target.files);
      this.uploadFile(0);

      document.getElementById('upload-file-input').value = null;
    },

    deleteFinish() {
      this.delete.loading = false;
      Nova.$toasted.success(`Видалено: ${this.delete.done}/${this.delete.total}`);

      this.fetchItems();

      if (this.view.item) {
        this.closeItem();
      }
    },
    deleteFile(index) {
      let item = this.delete.items[index];

      if (!item) {
        return this.deleteFinish();
      }

      Nova.request().delete('/nova-vendor/media/items/' + item.id)
        .then(r => {
          this.delete.done++;

          if (r.data.message) {
            Nova.$toasted.show(r.data.message);
          }

          this.deleteFile(index + 1);
        })
        .catch(e => {
          Nova.$toasted.error(e);

          this.deleteFile(index + 1);
        });
    },
    deleteFiles() {
      if (!confirm('Видалити вибрані зобреження?')) {
        return;
      }

      this.delete.done = 0;
      this.delete.items = this.selections.items;
      this.delete.total = this.delete.items.length;
      this.delete.loading = true;

      this.selections.items = [];

      this.deleteFile(0);
    },
  }
}
</script>

<style>
.media-card {
  min-height: 300px;
}

.media-view-card {
  min-height: 300px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: stretch;
  justify-self: center;
  align-self: center;
}

.media-view-content {
  width: 100%;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  flex-wrap: wrap;
  align-items: center;
  justify-self: center;
  align-self: center;
  gap: 16px;
  padding: 16px 16px 16px 16px;
}

.media-view-details {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex-wrap: wrap;
  align-items: stretch;
  justify-self: center;
  align-self: center;
  gap: 8px;
}

.media-view-details-row {
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: flex-start;
  flex-wrap: wrap;
}

.media-view-details-row-header {
  width: 100px;
  font-style: normal;
  font-weight: 600;
  font-size: 16px;
  text-align: start;
  max-lines: 1;
  overflow: hidden;
  text-overflow: ellipsis;
}

.media-view-details-row-data {
  font-style: normal;
  font-weight: 400;
  font-size: 16px;
  text-align: start;
  overflow: hidden;
  text-overflow: ellipsis;
}

.media-view-img-div {
  flex-grow: 2;
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
}

.media-view-img {
  background-image: linear-gradient(45deg, rgba(241, 245, 249, 0.5), rgba(100, 116, 139, 0.4));
  border-radius: 4px;
}

.media-view-link {
  font-style: normal;
  font-weight: 600;
  font-size: 16px;
  color: rgba(var(--colors-primary-500), 100%);
}

.media-actions {
  width: 100%;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  padding: 8px 8px 8px 8px;
}

.media-actions-group {
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: center;
  gap: 4px;
}

.media-filters {
  width: 100%;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  padding: 8px 8px 8px 8px;
}

.media-filters-input {
  min-width: 120px;
  max-width: 300px;
}

.media-filters-group {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 16px;
}

.media-list {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: stretch;
  align-items: flex-start;
  gap: 8px;
  padding: 16px 16px 16px 16px;
}

.media-list-item {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  gap: 2px;
}

.media-list-item-img {
  width: 100px;
  height: 100px;
  border-radius: 4px;
  object-fit: cover;
  background-image: linear-gradient(45deg, rgba(241, 245, 249, 0.5), rgba(100, 116, 139, 0.4));
}

.media-list-selection {
  border: 4px solid rgba(var(--colors-primary-500), 100%);
}

.media-list-item-name {
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

.media-pagination {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  margin-top: 16px;
  border-radius: 4px;
}

.media-pagination-button {

}

.media-pagination-button:disabled,
.media-pagination-button[disabled] {
  opacity: 40%;
}

.media-no-results {
  display: flex;
  flex-direction: column;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 48px 64px 64px 64px;
}

.media-no-results-img {
  width: 100px;
  height: 100px;
}

.media-no-results-text {
  font-style: normal;
  font-weight: 500;
  font-size: 24px;
  line-height: 28px;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
