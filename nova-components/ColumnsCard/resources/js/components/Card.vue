<template>
  <card class="container h-auto w-full flex flex-col" id="columns-card-container">

    <div class="header flex justify-between items-center">

      <div class="header-collapse flex justify-start items-center">
        <button class="btn-collapse flex justify-center items-center rounded"
                :class="{'rotated': !isCollapsed}"
                @click="isCollapsed = !isCollapsed">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4.46967 7.96967C4.73594 7.7034 5.1526 7.6792 5.44621 7.89705L5.53033 7.96967L12 14.439L18.4697 7.96967C18.7359 7.7034 19.1526 7.6792 19.4462 7.89705L19.5303 7.96967C19.7966 8.23594 19.8208 8.6526 19.6029 8.94621L19.5303 9.03033L12.5303 16.0303C12.2641 16.2966 11.8474 16.3208 11.5538 16.1029L11.4697 16.0303L4.46967 9.03033C4.17678 8.73744 4.17678 8.26256 4.46967 7.96967Z" fill="currentColor"/>
          </svg>
        </button>

        <h3 class="text-md font-semibold cursor-pointer"
            @click="isCollapsed = !isCollapsed">
          {{ settings.title }}
        </h3>
      </div>

      <button v-if="!isCollapsed"
        class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"
        @click="applyFields">
        {{ settings?.button?.apply ?? 'Apply' }}
      </button>
    </div>

    <div v-if="!isCollapsed"
        class="columns w-full flex flex-wrap justify-start items-start">
      <template v-for="(column, index) in columns" :key="index">
        <div class="column">

          <template v-for="field in column" :key="field.attribute">
            <label :for="field.attribute" class="label flex items-center">

              <input class="checkbox cursor-pointer" type="checkbox"
                     :checked="field.checked"
                     @change="toggleField(field)">

              <span class="column-label cursor-pointer font-semibold"
                    @click="toggleField(field)">
                {{ field.label }}
              </span>

            </label>
          </template>

        </div>
      </template>
    </div>

  </card>
</template>

<script>
import isEmpty from "lodash";
import Filterable from "../../../../../vendor/laravel/nova/resources/js/mixins/Filterable.js";
import {escapeUnicode} from "../../../../../vendor/laravel/nova/resources/js/util/escapeUnicode.js";

export default {
  props: [
    'card',
    // The following props are only available on resource detail cards...
    'resource',
    'resourceId',
    'viaResource',
    'viaResourceId',
    'viaRelationship',
    // The following props are available on index as well...
    'resourceName',
  ],
  mixins: [Filterable],
  data() {
    const settings = this.card.settings;
    const fields = this.relevantFields(this.card.fields, this.cachedAttributes());
    const attributes = this.getCheckedAttributes(fields);

    return {
      settings: settings,
      fields: fields,
      attributes: attributes,
      filterIsActive: false,
      isCollapsed: true,
      numberOfColumns: fields.length > 4 ? 4 : fields.length,
    }
  },
  mounted() {
    this.applyFields();

    console.log('fields: ', this.fields);
  },
  computed: {
    columns() {
      return this.splitOnColumns(this.fields, this.numberOfColumns);
    },
  },
  methods: {
    splitOnColumns(items, numberOfColumns) {
      const columns = [];
      const numberOfRows = Math.ceil(items.length / numberOfColumns);

      for (let i = 0; i < numberOfColumns; i++) {
        let column = [];

        for (let j = 0; j < numberOfRows; j++) {
          let index = i * numberOfRows + j;

          if (index >= items.length) {
            break;
          }

          if (!items[index]) {
            continue;
          }

          column.push(items[index]);
        }

        columns.push(column);
      }

      console.log({numberOfColumns, numberOfRows, columns});

      return columns;
    },
    getChecked(fields) {
      return fields.filter(f => {
        return f.checked;
      });
    },
    getAttributes(fields) {
      return fields.map(f => {
        return f.attribute;
      })
    },
    getCheckedAttributes(fields) {
      return this.getAttributes(this.getChecked(fields));
    },

    applyFields() {
      const query = this.getEncodedQueryString();
      this.cache(this.settings.cache.key, {
        query: query,
        fields: this.fields,
        attributes: this.attributes
      });

      this.updateColumnsFilter();
    },
    toggleField(field) {
      field.checked = !field.checked;

      if (field.checked) {
        if (!this.attributes.includes(field.attribute)) {
          this.attributes.push(field.attribute);
        }
      } else if (this.attributes.includes(field.attribute)) {
        this.attributes = this.attributes.filter(a => a !== field.attribute);
      }
    },
    relevantFields(provided, stored) {
      console.log('provided: ', provided);
      console.log('stored: ', stored);

      if (!Array.isArray(stored) || !stored.length) {
        return provided;
      }
      let fields = !Array.isArray(provided) || !provided.length ? [] : provided;
      for (const field of fields) {
        field.checked = stored.includes(field.attribute)
      }

      console.log('relevant: ', fields);

      return fields;
    },

    updateColumnsFilter() {
      const filter = this.$store.getters[`${this.resourceName}/getFilter`](this.settings.filter.class);
      if (filter == null) {
        console.error('There is no filter for ColumnsCard');
        return;
      }

      if (!this.arrayCompare(filter.currentValue, this.attributes)) {
        console.log('ColumnsFilter have changed...');
        filter.currentValue = this.attributes.map(a => a);
      } else {
        console.log('ColumnsFilter haven\'t changed...');
      }
    },
    updateQueryString(value) {
      const key = this.filterParameter;
      const url = new URL(window.location.href);

      try {
        if (url.searchParams.get(key) === value[key]) {
          return;
        }
      } catch (error) {
        //
      }

      url.searchParams.set(key, value[key]);
      this.$inertia.get(url);
    },

    cache(cacheKey, value, property = undefined) {
      try {
        if (property == null) {
          localStorage.setItem(cacheKey, JSON.stringify(value));
          return true;
        }
        const item = JSON.parse(localStorage.getItem(cacheKey));
        item[property] = value;
        return this.cache(cacheKey, item);
      } catch (error) {
        return false;
      }
    },
    cached(cacheKey, property = undefined) {
      try {
        const item = JSON.parse(localStorage.getItem(cacheKey));
        return property == null ? item : item[property];
      } catch (error) {
        return null
      }
    },

    decodeObject(data) {
      try {
        return JSON.parse(atob(data))
      } catch (e) {
        return {}
      }
    },
    encodeObject(data) {
      return btoa(escapeUnicode(JSON.stringify(data)));
    },

    cachedAttributes() {
      return this.cached(this.card.settings.cache.key, 'attributes');
    },
    getEncodedQueryString() {
      return this.encodeObject({...this.attributes})
    },
    cachedEncodedQueryString() {
      return this.cached(this.card.settings.cache.key, 'query');
    },

    arrayCompare(left, right) {
      if (!Array.isArray(left) || !Array.isArray(right) || left.length !== right.length) {
        return false;
      }

      // .concat() to not mutate arguments
      const arr1 = left.concat().sort();
      const arr2 = right.concat().sort();

      for (let i = 0; i < arr1.length; i++) {
        if (arr1[i] !== arr2[i]) {
          return false;
        }
      }

      return true;
    }
  },
}
</script>

<style scoped>
.container {
  padding: 12px;
  gap: 8px;
}

.header {
  gap: 8px;
}

.header-collapse {
  gap: 8px;
}

.btn-collapse {
  width: 36px;
  height: 36px;
  padding: 4px;
}

.btn-collapse:hover {
  background-color: rgba(0, 0, 0, 20%);
}

.rotated {
  transform: rotate(180deg);
}

.columns {
  gap: 8px;
}

.column {
  justify-content: start;
  align-items: start;
  flex-grow: 1;
  gap: 8px;
}

.column-label {
  padding-left: 8px;
}
</style>
