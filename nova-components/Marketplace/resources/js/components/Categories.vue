<template>
  <div class="horizontal categories">
    <section class="categories-item" :class="{active: isSelected(c)}"
             @click="this.$emit('toggle-category', c)"
             v-for="c in this.categories"
             v-if="this.categories">
      <img class="categories-item-img" :alt="c.title"
           :src="c.media && c.media.length ? c.media[0].url : this.tab.image"/>
      <span class="categories-item-span">
        {{ c.title }}
      </span>
    </section>
  </div>
</template>

<script>
export default {
  name: 'Categories',
  props: {
    tab: Object,
    category: Object,
    categories: Array,
  },
  emits: ['toggle-category'],
  methods: {
    isSelected(category) {
      return this.category && this.category.id === category.id;
    },
  },
}
</script>

<style scoped>
.categories {
  display: flex;
  flex-direction: row;
  align-self: center;
  justify-self: center;
  margin-bottom: 16px;
}

.categories-item {;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex-shrink: 0;
  align-items: center;
  width: 110px;
  height: 110px;
  margin-left: 8px;
  margin-right: 8px;
  padding: 8px 8px 8px 8px;
  border-radius: 4px;
  text-align: center;
  user-select: none;
  cursor: pointer;
}

.categories-item-img {
  width: 94px;
  height: 70px;
}

.categories-item-span {
  height: 16px;
  align-self: center;
  justify-self: center;
  font-style: normal;
  font-weight: 400;
  font-size: 14px;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
