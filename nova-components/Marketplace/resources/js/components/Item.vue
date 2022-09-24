<template>
  <div class="list-item">
    <img class="list-item-img" :alt="item.title"
         :src="item.media && item.media.length ? item.media[0].url : tab.image"/>
    <div class="list-item-details">
      <span class="list-item-title">
        {{ item.title }}
      </span>
      <span class="list-item-description">
        {{ item.description }}
      </span>

      <div class="list-item-info">
        <span class="list-item-weight" v-show="item.weight">
          {{ item.weight < 1000 ? item.weight + 'g' : (item.weight / 1000.0).toFixed(2) + 'kg' }}
        </span>
        <span class="list-item-price" :class="{'list-item-price-centered': !item.weight}">
          {{ priceOf(item) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Item',
  props: {
    tab: Object,
    item: Object,
  },
  methods: {
    priceOf(item) {
      if (!item) {
        return '';
      }

      if (item.price === 0) {
        return 'Free';
      } else if (item.price > 0) {
        return '$' + item.price;
      }

      let price = null;
      if (item.once_paid_price) {
        price = '$' + item.once_paid_price;
      }
      if (item.hourly_paid_price) {
        price = (price ? price + ' + ' : '') + '$' + item.hourly_paid_price + '/hour';
      }
      return price ?? '';
    }
  }
}
</script>

<style scoped>
.list-item {
  height: 100px;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  gap: 12px;
  align-items: center;
  justify-content: space-between;
  padding: 8px;
  background: #FFFFFF;
  border-radius: 4px;
}

.list-item-img {
  width: 72px;
  height: 72px;
  user-select: none;
  cursor: pointer;
}

.list-item-details {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  flex-wrap: wrap;
  gap: 4px;
  flex-basis: 128px;
  align-items: center;
  justify-content: space-between;
}

.list-item-title {
  height: 18px;
  display: block;
  font-style: normal;
  font-weight: 400;
  font-size: 16px;
  line-height: 18px;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
}

.list-item-description {
  height: 48px;
  display: block;
  max-lines: 3;
  line-clamp: 3;
  font-style: normal;
  font-weight: 300;
  font-size: 14px;
  line-height: 16px;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
}

.list-item-info {
  display: flex;
  flex-direction: row;
  align-self: stretch;
  justify-self: stretch;
  padding: 0 8px 0 8px;
}

.list-item-weight {
  display: block;
  flex-grow: 1;
  font-style: normal;
  font-weight: 300;
  font-size: 14px;
  line-height: 16px;
  text-align: start;
  overflow: hidden;
  text-overflow: ellipsis;
}

.list-item-price {
  display: block;
  flex-grow: 1;
  font-style: normal;
  font-weight: 400;
  font-size: 16px;
  line-height: 18px;
  text-align: end;
  overflow: hidden;
  text-overflow: ellipsis;
}

.list-item-price-centered {
  text-align: center;
}
</style>
