<template>
  <div class="no-results" v-if="tab && tab.items && (!tab.items.data || !tab.items.data.length)">
    <img class="no-results-img"
         alt="No results"
         :src="tab.image"/>
    <span class="no-results-text">
      No results...
    </span>
  </div>
</template>

<script>
import {mapGetters} from "vuex";

export default {
  name: 'NoResults',
  computed: {
    ...mapGetters({
      tab: "andriichello/marketplace/getTab",
    })
  }
}
</script>

<style scoped>
.no-results {
  display: flex;
  flex-direction: column;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 64px 64px 64px 64px;
}

.no-results-img {
  width: 100px;
  height: 100px;
}

.no-results-text {
  font-style: normal;
  font-weight: 500;
  font-size: 24px;
  line-height: 28px;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
