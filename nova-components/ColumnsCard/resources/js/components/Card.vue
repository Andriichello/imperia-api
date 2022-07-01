<template>
  <card class="columns-card lex flex-col p-6">

    <div class="header">
      <h4>{{ settings.title }}</h4>

      <button
        class="flex-shrink-0 shadow rounded focus:outline-none ring-primary-200 dark:ring-gray-600 focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"
        @click="applyFields">
        Save
      </button>
    </div>

    <ul class="checkboxes">
      <li v-for="field of fields"
          id="fields" class="pt-1 pb-1">

        <label :for="field.attribute" class="label flex items-center">

          <input class="checkbox" type="checkbox"
                 :checked="field.checked"
                 @change="toggleField(field)">

          <span class="ml-3">{{ field.label }}</span>

        </label>
      </li>

    </ul>

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
    }
  },
  mounted() {
    this.applyFields();
  },
  methods: {
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

<style>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 10px;
}

.checkboxes {
  height: auto;
  column-count: 4;
  padding: 0;
  list-style-type: none;
}

.columns-card {
  height: auto
}

li {
  -webkit-column-break-inside: avoid;
  page-break-inside: avoid;
  break-inside: avoid;
}
</style>
