<template>
  <Block :title="title" :description="description">
    <div class="flex justify-start items-center">
      <span class="text-xl font-semibold">{{ value ?? '--' }}</span>
    </div>
  </Block>
</template>

<script>
import Block from "./Block.vue";

export default {
  name: 'ValueBlock',
  components: {Block},
  props: {
    value: {},
    title: {
      type: String,
    },
    description: {
      type: String,
      default: null
    }
  },
}
</script>
