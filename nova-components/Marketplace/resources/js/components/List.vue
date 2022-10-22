<template>
  <div class="list flex-gap">
    <div class="list-col" v-for="column in this.columns">
      <Item :tab="tab" :item="item" v-for="item in column"/>
    </div>
  </div>
</template>

<script>
import Item from "./Item";

export default {
  name: 'List',
  components: {
    Item,
  },
  props: {
    tab: Object,
    items: Object,
    numberOfColumns: {
      type: Number,
      default: 2,
    },
  },
  computed: {
    columns() {
      if (!this.items || !this.items.data) {
        this.splitOnColumns([], this.numberOfColumns)
      }

      return this.splitOnColumns(this.items.data ?? [], this.numberOfColumns);
    }
  },
  methods: {
    splitOnColumns(items, number) {
      const columns = [[], []]
      items.forEach((item, index) => {
        columns[index % number].push(item);
      });

      return columns;
    },
  }
}
</script>

<style scoped>
.list {
  display: flex;
  flex-basis: 236px;
  flex-grow: 1;
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
</style>
