<template>
  <div class="tabs">
    <div class="tabs-item" :class="{selected: isSelected(t)}"
         @click="this.$emit('select-tab', t)"
         v-for="t in this.tabs">
      <img class="tabs-item-img" :alt="t.title" :src="t.image"/>
      <span class="tabs-item-text">
        {{ t.title }}
      </span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Tabs',
  props: {
    tab: Object,
    tabs: Array,
  },
  emits: ['select-tab'],
  methods: {
    isSelected(tab) {
      return this.tab && this.tab.target === tab.target;
    },
  },
}
</script>

<style scoped>
.tabs {
  width: 100%;
  max-width: 1000px;
  display: flex;
  flex-direction: row;
  justify-content: stretch;
  align-items: stretch;

  margin-top: 8px;
  margin-bottom: 16px;
}

.tabs-item {
  display: flex;
  flex-direction: row;
  flex-grow: 1;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 8px 16px 8px 16px;
  cursor: pointer;
  border-bottom: 4px solid transparent;
}

.tabs-item.selected {
  border-bottom: 4px solid #F3DA8D;
}

.tabs-item-img {
  width: 32px;
  height: 32px;
}

.tabs-item-text {
  font-style: normal;
  font-weight: 500;
  font-size: 22px;
  line-height: 25px;
  text-align: center;
  max-lines: 1;
}

@media screen and (max-width: 600px) {
  .tabs-item {
    align-items: center;
    justify-content: center;
    gap: 0;
  }

  .tabs-item-img {
    width: 32px;
    height: 32px;
    align-self: center;
    justify-self: center;
  }

  .tabs-item-text {
    width: 0;
    flex-grow: 0;
    visibility: collapse;
  }
}
</style>
