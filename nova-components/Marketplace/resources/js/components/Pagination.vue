<template>
  <nav class="pagination" v-if="tab && tab.items && tab.items.data && tab.items.data.length">
    <div style="flex-grow: 1; flex-basis: 150px;">
      <button class="pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
              @click="this.$emit('load-page', tab.target, tab.items.meta.current_page)">
        ↻
      </button>
    </div>

    <div>
      <button class="pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
              :disabled="tab.items.meta.current_page <= 1"
              @click="this.$emit('load-page', tab.target, 1)">
        «
      </button>
      <button class="pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
              :disabled="tab.items.meta.current_page <= 1"
              @click="this.$emit('load-page', tab.target, tab.items.meta.current_page - 1)">
        ‹
      </button>
      <span class="text-sm text-80 px-4 ml-auto">
        {{ tab.items.meta.current_page }}
      </span>
      <button class="pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
              :disabled="tab.items.meta.current_page >= tab.items.meta.last_page"
              @click="this.$emit('load-page', tab.target, tab.items.meta.current_page + 1)">
        ›
      </button>

      <button class="pagination-button font-mono btn btn-link h-9 min-w-9 px-2 border-50 text-80 opacity-80"
              :disabled="tab.items.meta.current_page >= tab.items.meta.last_page"
              @click="this.$emit('load-page', tab.target, tab.items.meta.last_page)">
        »
      </button>
    </div>

    <div style="display: flex; justify-content: end; flex-grow: 1; flex-basis: 150px;">
      <span v-if="tab.items.data.length" class="text-sm text-80 px-4 ml-auto">
          {{ tab.items.meta.from }}-{{ tab.items.meta.to }} of {{ tab.items.meta.total }}
      </span>
    </div>
  </nav>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  name: 'Pagination',
  emits: ['load-page'],
  computed: {
    ...mapGetters({
      tab: "andriichello/marketplace/getTab",
    })
  }
}
</script>

<style scoped>
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
.pagination-button[disabled] {
  opacity: 40%;
}
</style>
