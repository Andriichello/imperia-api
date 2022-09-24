<template>
  <div class="filters">
    <div class="search">
      <div>
        <input class="search-input h-9 min-w-9 px-2 border-50 text-80 opacity-80"
               ref="search" type="text"
               placeholder="Enter search string..."
               :value="this.search"
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
</style>
