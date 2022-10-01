<template>
  <div class="horizontal menus">
    <section class="menus-item" :class="{active: isSelected(m)}"
             @click="selectMenu(m)"
             v-for="m in this.menus">
              <span class="menus-item-text">
                {{ m.title }}
              </span>
    </section>
  </div>
</template>

<script>
export default {
  name: 'Menus',
  props: {
    menu: Object,
    menus: Array,
  },
  emits: ['select-menu'],
  methods: {
    isSelected(menu) {
      return this.menu && this.menu.id === menu.id;
    },
    selectMenu(menu) {
      if (!this.menu || this.menu.id !== menu.id) {
        this.$emit('select-menu', menu);
      }
    }
  }
}
</script>

<style scoped>
.menus {
  display: flex;
  flex-direction: row;
  align-self: center;
  justify-self: center;
  margin-bottom: 16px;
}

.menus-item {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  margin-left: 16px;
  margin-right: 16px;
  padding: 8px 12px 8px 12px;
  border-radius: 4px;
  user-select: none;
  cursor: pointer;
}

.menus-item-text {
  font-style: normal;
  font-weight: 400;
  font-size: 22px;
  line-height: 25px;
  text-align: center;
}
</style>
