<template>
  <div class="filters">
    <div class="search">
      <div>
        <input class="w-full form-control form-input form-input-bordered"
               ref="search" type="text" placeholder="Search..." :value="this.search"
               @keyup.enter="applySearch(getSearch())">
      </div>
      <div style="margin-left: 4px">
        <button class="search-button btn btn-link min-w-9 border-50 text-80 opacity-80 bg-gray-100 dark:bg-gray-900 dark:focus:bg-gray-800 focus:bg-white focus:outline-none focus:ring focus:ring-primary-200 dark:focus:ring-gray-600"
                @click="applySearch(getSearch())">
          search
        </button>
        <button class="search-button btn btn-link min-w-9 border-50 text-80 opacity-80 bg-gray-100 dark:bg-gray-900 dark:focus:bg-gray-800 focus:bg-white focus:outline-none focus:ring focus:ring-primary-200 dark:focus:ring-gray-600"
                @click="applySearch('')">
          x
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";

export default {
  name: 'Filters',
  computed: {
    ...mapGetters({
      tab: 'andriichello/marketplace/getTab',
      search: 'andriichello/marketplace/getSearch',
    })
  },
  methods: {
    ...mapActions({
      applySearch: 'andriichello/marketplace/applySearch',
    }),
    getSearch() {
      return this.$refs && this.$refs.search ? this.$refs.search.value : null;
    },
  }
}
</script>

<style scoped>
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
  font-style: normal;
  font-weight: 400;
  font-size: 16px;
  line-height: 18px;
  text-align: start;
  max-lines: 1;
}

.form-input:focus {
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(3px + var(--tw-ring-offset-width)) rgba(243, 218, 141, 30%);
}

.form-input-bordered:focus {
  border-color: rgba(243, 218, 141, var(--tw-border-opacity));
}

.search-button {
  flex-basis: 60px;
  padding: 8px 12px 8px 12px;
  margin-right: 4px;
  border-radius: 4px;
  font-style: normal;
  font-weight: 400;
  font-size: 16px;
  line-height: 18px;
  text-align: center;
  max-lines: 1;
}

.search-button:focus {
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(3px + var(--tw-ring-offset-width)) rgba(243, 218, 141, 30%);
}
</style>
